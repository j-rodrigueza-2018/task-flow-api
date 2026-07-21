<?php

declare(strict_types=1);

namespace App\Domain\Repository;

use App\Domain\Entity\Board;
use App\Domain\Enum\BoardRole;

interface BoardRepository
{
    /**
     * Saves a board to the repository.
     * 
     * @param Board $board The board to save.
     * 
     * @return void
     */
    public function save(Board $board): void;

    /**
     * Deletes a board from the repository.
     * 
     * @param Board $board The board to delete.
     * 
     * @return void
     */
    public function delete(Board $board): void;

    /**
     * Finds a board by its ID.
     * 
     * @param string $id The ID of the board to find.
     * 
     * @return Board|null The found board or null if not found.
     */
    public function findById(string $id): ?Board;

    /**
     * Finds all boards in the repository.
     * 
     * @return Board[] An array of all boards.
     */
    public function findAll(): array;

    /**
     * Adds a user to a board.
     * 
     * @param string $board_id The ID of the board to add the user to.
     * @param string $user_id The ID of the user to add.
     * @param BoardRole $role The role of the user in the board.
     * 
     * @return void
     */
    public function addUserToBoard(string $board_id, string $user_id, BoardRole $role): void;
}
