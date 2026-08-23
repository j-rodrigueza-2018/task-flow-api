<?php

declare(strict_types=1);

namespace Tests\Unit\Application\UseCase\Board;

use App\Application\UseCase\Board\GetUserBoardsUseCase;
use App\Domain\Repository\BoardRepository;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetUserBoardsUseCaseTest extends TestCase
{
    private BoardRepository&MockObject $board_repository_mock;
    private GetUserBoardsUseCase $use_case;

    #[Override]
    protected function setUp(): void
    {
        $this->board_repository_mock = $this->createMock(BoardRepository::class);
        $this->use_case = new GetUserBoardsUseCase($this->board_repository_mock);
    }

    public function testItReturnsUserBoardsSuccessfully(): void
    {
        $user_id = uuid_create(UUID_TYPE_RANDOM);

        $expected_boards = [];

        $this->board_repository_mock
            ->expects($this->once())
            ->method('findByUserId')
            ->with($user_id)
            ->willReturn($expected_boards);

        $result = $this->use_case->execute($user_id);

        $this->assertIsArray($result);
        $this->assertSame($expected_boards, $result);
    }
}
