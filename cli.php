<?php
/**
 * 命令行执行
 * User: hikoqiu
 * Date: 2017/6/27
 */

include_once './src/autoloader.php';

use src\grid\Grid;
use src\algo\astar\AStar;

try {
    // 1.1 初始化表格
    $grid = new Grid(9, 10, [
        [0, 2],
        [0, 5],
        [1, 5],
        [2, 5],
        [4, 4],
        [5, 4],
        [6, 4],
        [7, 4],
        [8, 4],
        [8, 2],
    ]);
    $star = $grid->getNodeByXY(0, 2);
    $end  = $grid->getNodeByXY(6, 6);

    // 2.1 初始化算法
    $astar = new AStar($grid);
    $astar->setDiagonal(true);
    $pathNodes = $astar->calculatePath($star, $end);

    // 3.1 打印表格和结果
    echo "\n[表格]:\n\n";
    echo $grid->toString();
    if (empty($pathNodes)) {
        $result = "\n[结束]: 无路可走";
    } else {
        $result = "\n[最优]: ";
    }

    foreach ($pathNodes as $node) {
        $result .= sprintf('(%d , %d) -> ', $node->getX(), $node->getY());
    }
    echo rtrim($result, ' -> ') . "\n\n";

} catch (\Exception $e) {
    echo '[ERROR]: ' . $e->getMessage() . ".\n";
}
