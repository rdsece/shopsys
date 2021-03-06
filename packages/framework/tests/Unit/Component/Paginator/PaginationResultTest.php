<?php

namespace Tests\FrameworkBundle\Unit\Component\Paginator;

use PHPUnit\Framework\TestCase;
use Shopsys\FrameworkBundle\Component\Paginator\PaginationResult;

class PaginationResultTest extends TestCase
{
    public function getTestPageCountData()
    {
        return [
            [1, 10, 40, [], 4],
            [1, 10, 41, [], 5],
            [1, 10, 49, [], 5],
            [1, 10, 50, [], 5],
            [1, 10, 51, [], 6],
            [1, 10, 5, [], 1],
            [1, 0, 0, [], 0],
            [1, null, 5, [], 1],
            [1, null, 0, [], 0],
        ];
    }

    /**
     * @dataProvider getTestPageCountData
     * @param mixed $page
     * @param mixed $pageSize
     * @param mixed $totalCount
     * @param mixed $results
     * @param mixed $expectedPageCount
     */
    public function testGetPageCount($page, $pageSize, $totalCount, $results, $expectedPageCount)
    {
        $paginationResult = new PaginationResult($page, $pageSize, $totalCount, $results);

        $this->assertSame($expectedPageCount, $paginationResult->getPageCount());
    }
}
