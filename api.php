<?php
/**
 * 提供 HTTP 接口
 * User: hikoqiu
 * Date: 2017/6/27
 */

include_once './src/autoloader.php';

use src\utl\ResponseUtl;
use src\grid\Grid;
use src\algo\astar\AStar;
use src\grid\Node;

const DIAGONAL_YES = 1;
const DIAGONAL_NO  = 2;

try {
    // 1.1 获取参数
    $params = getParams();

    // 2.1 生成表格和初始化障碍点
    $grid = new Grid($params['x_num'], $params['y_num'], $params['blocks']);
    $star = $grid->getNodeByXY($params['start'][0], $params['start'][1]);
    $end  = $grid->getNodeByXY($params['end'][0], $params['end'][1]);
    if (!$star || !$end) {
        throw new Exception('起始点参数错误', ResponseUtl::CODE_FAIL);
    }

    // 开始计算
    $astar = new AStar($grid);
    $astar->setDiagonal($params['diagonal'] == DIAGONAL_YES);
    $pathNodes = $astar->calculatePath($star, $end);
    if (!$pathNodes) {
        echo ResponseUtl::fail(ResponseUtl::CODE_FAIL, '未找到可行路径');
        exit;
    }

    echo ResponseUtl::succ([
        'path_nodes' => Node::toPoints($pathNodes),
        'footprint'  => Node::toPoints($astar->getFootprint()),
    ]);
} catch (\Exception $e) {
    echo ResponseUtl::fail(ResponseUtl::CODE_FAIL, $e->getMessage());
}

/**
 * 获取参数并判断合法性
 * @return array
 */
function getParams()
{
    $params = [
        'start'    => '0,0',
        'end'      => '0,0',
        'x_num'    => 1,
        'y_num'    => 1,
        'diagonal' => DIAGONAL_NO,
        'blocks'   => [],
    ];
    isset($_GET['start']) && $params['start'] = explode('_', $_GET['start']);
    isset($_GET['end']) && $params['end'] = explode('_', $_GET['end']);
    isset($_GET['x_num']) && $params['x_num'] = intval($_GET['x_num']);
    isset($_GET['y_num']) && $params['y_num'] = intval($_GET['y_num']);
    isset($_GET['diagonal']) && $params['diagonal'] = intval($_GET['diagonal']);
    if (isset($_GET['blocks'])) {
        $blocks = array_filter(explode(';', $_GET['blocks']));
        foreach ($blocks as &$item) {
            $item = explode(',', $item);
        }
        $params['blocks'] = $blocks;
    }

    // @TODO 参数校验

    return $params;
}


