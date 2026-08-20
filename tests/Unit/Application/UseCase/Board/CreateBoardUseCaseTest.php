<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCase\Board;

use App\Application\UseCase\Board\CreateBoardUseCase;
use App\Domain\Entity\Board;
use App\Domain\Entity\BoardUser;
use App\Domain\Enum\BoardRole;
use App\Domain\Repository\BoardRepository;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreateBoardUseCaseTest extends TestCase
{
    private BoardRepository&MockObject $board_repository_mock;
    private CreateBoardUseCase $use_case;

    #[Override]
    protected function setUp(): void
    {
        $this->board_repository_mock = $this->createMock(BoardRepository::class);
        $this->use_case = new CreateBoardUseCase($this->board_repository_mock);
    }

    public function testItCreatesBoardSuccessfullyAndAssignsTheUserAsOwner(): void
    {
        $user_id = uuid_create(UUID_TYPE_RANDOM);
        $board_name = 'Test Board';
        $board_description = 'This is a test board.';

        // Verify that the Board is saved with the correct data.
        $this->board_repository_mock
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function (Board $board) use ($board_name, $board_description) {
                        return $board->getName() === $board_name &&
                            $board->getDescription() === $board_description;
                    }
                )
            );

        // Verify that the board is linked to the user with the OWNER role.
        $this->board_repository_mock
            ->expects($this->once())
            ->method('addUserToBoard')
            ->with(
                $this->callback(
                    function (BoardUser $board_user) use ($user_id) {
                        return $board_user->getUserId() === $user_id &&
                            $board_user->getRole() === BoardRole::OWNER;
                    }
                )
            );

        // Execute the use case
        $board = $this->use_case->execute($user_id, $board_name, $board_description);

        // Check that the returned board is an instance of Board
        $this->assertInstanceOf(Board::class, $board);

        // Check that the board has the correct name and description
        $this->assertEquals($board_name, $board->getName());
        $this->assertEquals($board_description, $board->getDescription());
    }
}
