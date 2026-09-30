<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCase\Board;

use App\Application\UseCase\Board\GetBoardUsersUseCase;
use App\Domain\Entity\BoardUser;
use App\Domain\Enum\BoardRole;
use App\Domain\Repository\BoardUserRepository;
use DateTimeImmutable;
use DomainException;
use InvalidArgumentException;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetBoardUsersUseCaseTest extends TestCase
{
    private BoardUserRepository&MockObject $board_user_repository_mock;
    private GetBoardUsersUseCase $use_case;

    #[Override]
    protected function setUp(): void
    {
        $this->board_user_repository_mock = $this->createMock(BoardUserRepository::class);
        $this->use_case = new GetBoardUsersUseCase($this->board_user_repository_mock);
    }

    public function testItThrowsExceptionIfBoardIdIsEmpty(): void
    {
        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('findByBoardAndUser');

        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('findByBoardId');

        try {
            $this->use_case->execute('', uuid_create(UUID_TYPE_RANDOM));
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('The board_id cannot be empty.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfRequesterIdIsEmpty(): void
    {
        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('findByBoardAndUser');

        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('findByBoardId');

        try {
            $this->use_case->execute(uuid_create(UUID_TYPE_RANDOM), '');
            $this->fail('Expected InvalidArgumentException was not thrown.');
        } catch (InvalidArgumentException $exception) {
            $this->assertEquals('The requester_id cannot be empty.', $exception->getMessage());
        }
    }

    public function testItThrowsExceptionIfRequesterDoesNotHavePermission(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);

        $this->board_user_repository_mock
            ->expects($this->once())
            ->method('findByBoardAndUser')
            ->with($board_id, $requester_id)
            ->willReturn(null);

        $this->board_user_repository_mock
            ->expects($this->never())
            ->method('findByBoardId');

        try {
            $this->use_case->execute($board_id, $requester_id);
            $this->fail('Expected DomainException was not thrown.');
        } catch (DomainException $exception) {
            $this->assertEquals('Requester does not have permission to access this board.', $exception->getMessage());
        }
    }

    public function testItReturnsBoardUsersSuccessfully(): void
    {
        $board_id = uuid_create(UUID_TYPE_RANDOM);
        $requester_id = uuid_create(UUID_TYPE_RANDOM);

        $this->board_user_repository_mock
            ->expects($this->once())
            ->method('findByBoardAndUser')
            ->with($board_id, $requester_id)
            ->willReturn(new BoardUser(
                id: uuid_create(UUID_TYPE_RANDOM),
                board_id: $board_id,
                user_id: $requester_id,
                role: BoardRole::MEMBER,
                created_at: new DateTimeImmutable(),
                updated_at: new DateTimeImmutable()
            ));

        $expected_board_users = [
            new BoardUser(
                id: uuid_create(UUID_TYPE_RANDOM),
                board_id: $board_id,
                user_id: uuid_create(UUID_TYPE_RANDOM),
                role: BoardRole::MEMBER,
                created_at: new DateTimeImmutable(),
                updated_at: new DateTimeImmutable()
            )
        ];

        $this->board_user_repository_mock
            ->expects($this->once())
            ->method('findByBoardId')
            ->with($board_id)
            ->willReturn($expected_board_users);

        $result = $this->use_case->execute($board_id, $requester_id);
        $this->assertIsArray($result);
        $this->assertSame($expected_board_users, $result);
    }
}
