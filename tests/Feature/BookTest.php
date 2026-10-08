<?php

namespace Tests\Feature;

use App\Models\Book;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Testy knih: CRUD, filtr a validacni pravidla ze zadani.
 */
class BookTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Pomocna metoda - vyrobi knihu s rozumnymi vychozimi hodnotami
     */
    private function book(array $data = []): Book
    {
        return Book::create(array_merge([
            'title' => 'Babička',
            'author' => 'Božena Němcová',
            'isbn' => '9788074990014',
            'available_copies' => 1,
        ], $data));
    }

    public function test_books_are_listed(): void
    {
        $this->book();

        $this->get(route('books.index'))->assertOk()->assertSee('Babička')->assertSee('Božena Němcová');
    }

    public function test_book_can_be_created(): void
    {
        $this->post(route('books.store'), [
            'title' => 'Hobit',
            'author' => 'J. R. R. Tolkien',
            'isbn' => '9788025703021',
            'available_copies' => 3,
        ])->assertRedirect(route('books.index'));

        $this->assertDatabaseHas('books', ['title' => 'Hobit', 'available_copies' => 3]);
    }

    public function test_book_can_be_updated(): void
    {
        $book = $this->book();

        $this->put(route('books.update', $book), [
            'title' => 'Babička (2. vydání)',
            'author' => 'Božena Němcová',
            'isbn' => $book->isbn,   // vlastni ISBN musi pri uprave projit
            'available_copies' => 2,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('books', ['id' => $book->id, 'title' => 'Babička (2. vydání)']);
    }

    public function test_book_can_be_deleted(): void
    {
        $book = $this->book();

        $this->delete(route('books.destroy', $book))->assertRedirect(route('books.index'));

        $this->assertModelMissing($book);
    }

    /**
     * ZADANI: filtr podle nazvu nebo autora
     */
    public function test_books_can_be_filtered_by_title_or_author(): void
    {
        $this->book(['title' => 'Hobit', 'author' => 'Tolkien', 'isbn' => '9788025703021']);
        $this->book(['title' => 'Babička', 'author' => 'Němcová', 'isbn' => '9788074990014']);

        // podle autora
        $this->get(route('books.index', ['search' => 'Tolkien']))->assertSee('Hobit')->assertDontSee('Babička');
        // podle nazvu
        $this->get(route('books.index', ['search' => 'Babička']))->assertSee('Babička')->assertDontSee('Hobit');
        // bez filtru se vypisou vsechny
        $this->get(route('books.index'))->assertSee('Hobit')->assertSee('Babička');
    }

    /**
     * ZADANI: ISBN ma 13 znaku
     */
    public function test_isbn_must_have_13_characters(): void
    {
        $this->post(route('books.store'), [
            'title' => 'Krátké ISBN', 'author' => 'A', 'isbn' => '123', 'available_copies' => 1,
        ])->assertSessionHasErrors('isbn');

        $this->assertDatabaseCount('books', 0);
    }

    /**
     * ZADANI: ISBN je jedinecne
     */
    public function test_isbn_must_be_unique(): void
    {
        $this->book(['isbn' => '9788074990014']);

        $this->post(route('books.store'), [
            'title' => 'Duplikát', 'author' => 'A', 'isbn' => '9788074990014', 'available_copies' => 1,
        ])->assertSessionHasErrors('isbn');

        $this->assertDatabaseCount('books', 1);
    }

    /**
     * ZADANI: available_copies je cele cislo nejmene 0
     */
    public function test_available_copies_cannot_be_negative(): void
    {
        $this->post(route('books.store'), [
            'title' => 'Mínus', 'author' => 'A', 'isbn' => '9788074990015', 'available_copies' => -1,
        ])->assertSessionHasErrors('available_copies');
    }

    public function test_available_copies_can_be_zero(): void
    {
        $this->post(route('books.store'), [
            'title' => 'Nula', 'author' => 'A', 'isbn' => '9788074990016', 'available_copies' => 0,
        ])->assertSessionHasNoErrors();
    }
}
