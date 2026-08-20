<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCase\Board;

use App\Application\UseCase\Board\AddUserToBoardUseCase;
use App\Domain\Entity\Board;
use App\Domain\Entity\BoardUser;
use App\Domain\Enum\BoardRole;
use App\Domain\Repository\BoardRepository;
use App\Domain\Repository\BoardUserRepository;
use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class AddUserToBoardUseCaseTest extends TestCase
{
    private BoardRepository&MockObject $board_repository_mock;
    private BoardUserRepository&MockObject $board_user_repository_mock;
    private AddUserToBoardUseCase $use_case;

    #[Override]
    protected function setUp(): void
    {
        $this->board_repository_mock = $this->createMock(BoardRepository::class);
        $this->board_user_repository_mock = $this->createMock(BoardUserRepository::class);

        $this->use_case = new AddUserToBoardUseCase(
            $this->board_repository_mock,
            $this->board_user_repository_mock
        );
    }

    public function testItThrowsExceptionIfBoardIsNotFound(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);

        $this->board_repository_mock
            ->expects($this->once())
            ->method('findById')
            ->with($board_id)
            ->willReturn(null);

        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('findByBoardAndUser');

        try {
            $this->use_case->execute($board_id, uuid_create(UUID_TYPE_RANDOM), BoardRole::MEMBER);
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('Board not found.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfUserIsAlreadyMember(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $dummy_board = new Board(
            id: $board_id,
            name: 'Test Board',
            description: null,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->board_repository_mock
            ->expects($this->once())
            ->method('findById')
            ->with($board_id)
            ->willReturn($dummy_board);

        $user_id = uuid_create(UUID_TYPE_RANDOM);
        $dummy_board_user = new BoardUser(
            id: uuid_create(UUID_TYPE_RANDOM),
            board_id: $board_id,
            user_id: $user_id,
            role: BoardRole::MEMBER,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->board_user_repository_mock
            ->expects($this->once())
            ->method('findByBoardAndUser')
            ->with($board_id, $user_id)
            ->willReturn($dummy_board_user);

        try {
            $this->use_case->execute($board_id, $user_id, BoardRole::MEMBER);
            $this->fail('Expected DomainException was not thrown.');
        } catch (DomainException $exception) {
            $this->assertEquals('The user is already a member of this board.', $exception->getMessage());
        }
    }

    public function testItAddsUserToBoardSuccessfully(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $user_id = uuid_create(UUID_TYPE_RANDOM);

        $dummy_board = new Board(
            id: $board_id,
            name: 'Test Board',
            description: null,
            created_at: new DateTimeImmutable(),
            updated_at: new DateTimeImmutable()
        );

        $this->board_repository_mock
            ->expects($this->once())
            ->method('findById')
            ->with($board_id)
            ->willReturn($dummy_board);

        $this->board_user_repository_mock
            ->expects($this->once())
            ->method('findByBoardAndUser')
            ->with($board_id, $user_id)
            ->willReturn(null);

        $this->board_user_repository_mock
            ->expects($this->once())
            ->method('save')
            ->with(
                $this->callback(
                    function (BoardUser $board_user) use ($board_id, $user_id) {
                        return $board_user->getBoardId() === $board_id
                            && $board_user->getUserId() === $user_id
                            && $board_user->getRole() === BoardRole::MEMBER;
                    }
                )
            );

        $result = $this->use_case->execute($board_id, $user_id, BoardRole::MEMBER);

        $this->assertInstanceOf(BoardUser::class, $result);
        $this->assertEquals($board_id, $result->getBoardId());
        $this->assertEquals($user_id, $result->getUserId());
        $this->assertEquals(BoardRole::MEMBER, $result->getRole());
    }
}
