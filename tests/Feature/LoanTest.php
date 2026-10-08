<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Reader;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testy vypujcek: pujceni, vraceni, vypujcky po terminu
 * a dve pravidla ze zadani, ktera se hlidaji dotazem do databaze.
 */
class LoanTest extends TestCase
{
    use RefreshDatabase;

    private function book(array $data = []): Book
    {
        return Book::create(array_merge([
            'title' => 'Hobit',
            'author' => 'Tolkien',
            'isbn' => '9788025703021',
            'available_copies' => 1,
        ], $data));
    }

    private function reader(array $data = []): Reader
    {
        static $i = 0;
        $i++;

        return Reader::create(array_merge([
            'name' => "Čtenář {$i}",
            'email' => "ctenar{$i}@example.com",
        ], $data));
    }

    /**
     * Data pro formular vypujcky
     */
    private function loanData(Book $book, Reader $reader, array $data = []): array
    {
        return array_merge([
            'book_id' => $book->id,
            'reader_id' => $reader->id,
            'borrowed_at' => now()->toDateString(),
            'due_at' => now()->addMonth()->toDateString(),
        ], $data);
    }

    public function test_loan_can_be_created_and_takes_one_copy(): void
    {
        $book = $this->book(['available_copies' => 2]);
        $reader = $this->reader();

        $this->post(route('loans.store'), $this->loanData($book, $reader))->assertSessionHasNoErrors();

        $this->assertDatabaseCount('loans', 1);
        $this->assertSame(1, $book->fresh()->available_copies);
    }

    /**
     * ZADANI: zalozeni vypujcky pouze tehdy, je-li dostupny vytisk
     */
    public function test_loan_is_rejected_when_no_copy_is_available(): void
    {
        $book = $this->book(['available_copies' => 0]);

        $this->post(route('loans.store'), $this->loanData($book, $this->reader()))
            ->assertSessionHasErrors('book_id');

        $this->assertDatabaseCount('loans', 0);
    }

    /**
     * ZADANI: stejnou knihu nelze jednomu ctenari pujcit podruhe,
     * dokud ji nevratil
     */
    public function test_same_reader_cannot_borrow_same_book_twice(): void
    {
        $book = $this->book(['available_copies' => 5]);
        $reader = $this->reader();

        $this->post(route('loans.store'), $this->loanData($book, $reader))->assertSessionHasNoErrors();
        $this->post(route('loans.store'), $this->loanData($book, $reader))->assertSessionHasErrors('reader_id');

        $this->assertDatabaseCount('loans', 1);
        // odmitnuta vypujcka nesmi odebrat vytisk
        $this->assertSame(4, $book->fresh()->available_copies);
    }

    public function test_different_readers_can_borrow_the_same_book(): void
    {
        $book = $this->book(['available_copies' => 2]);

        $this->post(route('loans.store'), $this->loanData($book, $this->reader()))->assertSessionHasNoErrors();
        $this->post(route('loans.store'), $this->loanData($book, $this->reader()))->assertSessionHasNoErrors();

        $this->assertDatabaseCount('loans', 2);
        $this->assertSame(0, $book->fresh()->available_copies);
    }

    /**
     * ZADANI: due_at je pozdeji nez borrowed_at
     */
    public function test_due_date_must_be_after_borrowed_date(): void
    {
        $book = $this->book();

        $this->post(route('loans.store'), $this->loanData($book, $this->reader(), [
            'due_at' => now()->subDay()->toDateString(),
        ]))->assertSessionHasErrors('due_at');

        $this->assertDatabaseCount('loans', 0);
    }

    /**
     * ZADANI: vraceni knihy
     */
    public function test_returning_a_book_gives_the_copy_back(): void
    {
        $book = $this->book(['available_copies' => 1]);
        $this->post(route('loans.store'), $this->loanData($book, $this->reader()));
        $loan = Loan::first();

        $this->patch(route('loans.return', $loan));

        $this->assertNotNull($loan->fresh()->returned_at);
        $this->assertSame(1, $book->fresh()->available_copies);
    }

    public function test_reader_can_borrow_again_after_returning(): void
    {
        $book = $this->book(['available_copies' => 1]);
        $reader = $this->reader();
        $this->post(route('loans.store'), $this->loanData($book, $reader));
        $this->patch(route('loans.return', Loan::first()));

        // po vraceni uz pravidlo "nevracena kniha" neplati
        $this->post(route('loans.store'), $this->loanData($book, $reader))->assertSessionHasNoErrors();

        $this->assertDatabaseCount('loans', 2);
    }

    public function test_returning_twice_does_not_add_a_copy_twice(): void
    {
        $book = $this->book(['available_copies' => 1]);
        $this->post(route('loans.store'), $this->loanData($book, $this->reader()));
        $loan = Loan::first();

        $this->patch(route('loans.return', $loan));
        $this->patch(route('loans.return', $loan));

        $this->assertSame(1, $book->fresh()->available_copies);
    }

    /**
     * ZADANI: zobrazeni vypujcek po terminu
     */
    public function test_overdue_loan_is_detected_and_shown(): void
    {
        $book = $this->book();
        $reader = $this->reader(['name' => 'Pozdní Čtenář']);
        $loan = Loan::create($this->loanData($book, $reader, [
            'borrowed_at' => now()->subDays(40),
            'due_at' => now()->subDays(10),
        ]));

        $this->assertTrue($loan->isOverdue());
        $this->assertSame(1, Loan::overdue()->count());

        $this->get(route('loans.index'))->assertOk()->assertSee('Pozdní Čtenář')->assertSee('Po termínu (1)');
    }

    public function test_returned_loan_is_not_overdue(): void
    {
        $loan = Loan::create($this->loanData($this->book(), $this->reader(), [
            'borrowed_at' => now()->subDays(40),
            'due_at' => now()->subDays(10),
            'returned_at' => now(),
        ]));

        $this->assertFalse($loan->isOverdue());
        $this->assertSame(0, Loan::overdue()->count());
    }

    /**
     * ZADANI: detail knihy se seznamem vypujcek
     */
    public function test_book_detail_shows_its_loans(): void
    {
        $book = $this->book();
        $reader = $this->reader(['name' => 'Karel Čtenář']);
        $this->post(route('loans.store'), $this->loanData($book, $reader));

        $this->get(route('books.show', $book))->assertOk()->assertSee('Hobit')->assertSee('Karel Čtenář');
    }

    public function test_deleting_a_book_deletes_its_loans(): void
    {
        $book = $this->book();
        $this->post(route('loans.store'), $this->loanData($book, $this->reader()));

        $this->delete(route('books.destroy', $book));

        $this->assertDatabaseCount('loans', 0);
    }

    public function test_deleting_a_reader_deletes_their_loans(): void
    {
        $reader = $this->reader();
        $this->post(route('loans.store'), $this->loanData($this->book(), $reader));

        $this->delete(route('readers.destroy', $reader));

        $this->assertDatabaseCount('loans', 0);
    }
}
