/**
 * Created by hikoqiu on 2017/6/28.
 */

// 选择节点的角色
const OP_SELECT_START = 1;
const OP_SELECT_END = 2;
const OP_SELECT_BLOCK = 3;

// 是否走斜角
const DIAGONAL_YES = 1;
const DIAGONAL_NO = 2;

const FOOTPRINT_TIME = 100; // ms

var PfPath = {
    Grid: {
        // 表格相关配置
        config: {
            startPoint: [],
            endPoint: [],
            xNum: 10,
            yNum: 10,
            xScore: 10,
            yScore: 10,
            diagonal: false,
            blocks: {},
        },

        // 页面元素
        elements: {
            grid: null
        },

        // 计算结果
        result: {
            pathNodes: [],
            footprint: []
        },

        // 当选选择的点的角色
        curOp: OP_SELECT_START,

        //  初始化方法
        init: function () {
            this.elements.grid = document.getElementById('grid');

            this.resetConfig();
            this.drawGrid();
        },

        /**
         * 获取页面配置
         * @returns {{}}
         */
        resetConfig: function () {
            this.config.xNum = parseInt(document.getElementById('x_num').value);
            this.config.yNum = parseInt(document.getElementById('y_num').value);
            this.config.xScore = parseInt(document.getElementById('x_score').value);
            this.config.yScore = parseInt(document.getElementById('y_score').value);

            // 重置所选配置
            this.config.startPoint = [];
            this.config.endPoint = [];
            this.config.blocks = {};
            this.result.pathNodes = [];
            this.result.footprint = [];
            console.log("config: ", this.config);
        },

        /**
         * 计算每个格子的宽高
         * [width, height]
         * @returns {*[]}
         */
        calculateItemWH: function () {
            var bodyWidth = document.body.clientWidth - 400;
            var bodyHeight = document.body.clientHeight - 150;
            var bdRadio = bodyWidth / bodyHeight;

            var xUnitNum = this.config.xNum * this.config.xScore;
            var yUnitNum = this.config.yNum * this.config.yScore;
            var unitStdLen = 0;
            var unRadio = xUnitNum / yUnitNum;
            if (unRadio > bdRadio) {
                unitStdLen = bodyWidth / xUnitNum;
            } else {
                unitStdLen = bodyHeight / yUnitNum;
            }
            return [
                this.config.xScore * unitStdLen, this.config.yScore * unitStdLen
            ];
        },

        /**
         *  表格打印
         */
        drawGrid: function () {
            // 计算每个格子的合适宽高
            var wdResult = this.calculateItemWH();
            var widthPx = wdResult[0];
            var heightPx = wdResult[1];

            // 组装表格
            var gridInnerHtml = '';
            for (var y = 0; y < this.config.yNum; y++) {
                var itemAddCls = (y == this.config.yNum - 1) ? 'item-bottom-line' : '';
                gridInnerHtml += '<div class="row">';
                for (var x = 0; x < this.config.xNum; x++) {
                    var itemXY = x + '-' + y;
                    var itemStyle = 'width: ' + widthPx + 'px;height: ' + heightPx + 'px;'
                    gridInnerHtml += '<div onclick="PfPath.Grid.clickItem(' + x + ',' + y + ');" id="id_' + itemXY + '" class="item ' + itemAddCls + '" style="' + itemStyle + '"></div>';
                }
                gridInnerHtml += '<div class="clearfix"></div></div>';
            }

            this.elements.grid.innerHTML = gridInnerHtml;
        },

        /**
         * 获取新配置, 重新绘图
         */
        redrawGrid: function () {
            this.resetConfig();
            this.drawGrid();
        },

        /**
         * 点击节点
         * @param x
         * @param y
         */
        clickItem: function (x, y) {
            console.log(x, y);

            switch (this.curOp) {
                case OP_SELECT_START:
                    this.setStart(x, y);
                    break;

                case OP_SELECT_END:
                    this.setEnd(x, y);
                    break;

                case OP_SELECT_BLOCK:
                    this.setBlock(x, y);
                    break;

                default:
                    alert('无效操作!');
            }
        },

        /**
         * 设置起点
         * @param x
         * @param y
         */
        setStart: function (x, y) {
            // 如果已经计算过路径, 则清除掉
            if (this.result.pathNodes.length > 0) {
                this.clearPath();
            }
            if (this.isEndPoint([x, y]) || this.isBlock(x, y)) {
                alert('提示: 起点不允许落在终点或障碍点上.');
                return;
            }

            this.rmOldStart();
            this.config.startPoint = [x, y];
            DomUtl.addCls(document.getElementById(this.genItemId(x, y)), 'start-item');
        },

        rmOldStart: function () {
            if (this.config.startPoint.length == 0) {
                return;
            }
            var oldEle = document.getElementById(this.genItemId(this.config.startPoint[0], this.config.startPoint[1]))
            DomUtl.rmCls(oldEle, 'start-item');
            this.config.startPoint = [];
        },

        /**
         * 设置终点
         * @param x
         * @param y
         */
        setEnd: function (x, y) {
            // 如果已经计算过路径, 则清除掉
            if (this.result.pathNodes.length > 0) {
                this.clearPath();
            }
            if (this.isStartPoint([x, y]) || this.isBlock(x, y)) {
                alert('提示: 终点不允许落在起点或障碍点上.');
                return;
            }

            this.rmOldEnd();
            this.config.endPoint = [x, y];
            DomUtl.addCls(document.getElementById(this.genItemId(x, y)), 'end-item');
        },

        rmOldEnd: function () {
            if (this.config.endPoint.length == 0) {
                return;
            }

            var oldEle = document.getElementById(this.genItemId(this.config.endPoint[0], this.config.endPoint[1]));
            DomUtl.rmCls(oldEle, 'end-item');
            this.config.endPoint = [];
        },

        /**
         * 设置障碍点
         * @param x
         * @param y
         */
        setBlock: function (x, y) {
            // 如果已经计算过路径, 则清除掉
            if (this.result.pathNodes.length > 0) {
                this.clearPath();
            }

            if (this.isStartPoint([x, y]) || this.isEndPoint([x, y])) {
                alert('提示: 障碍点不允许落在起始点上.');
                return;
            }

            if (this.isBlock(x, y)) {
                this.rmBlock(x, y);
                return;
            }

            this.config.blocks[this.genBlockKey(x, y)] = [x, y];
            DomUtl.addCls(document.getElementById(this.genItemId(x, y)), 'block-item');
        },

        rmBlock: function (x, y) {
            var ele = document.getElementById(this.genItemId(x, y));
            DomUtl.rmCls(ele, 'block-item');
            delete this.config.blocks[this.genBlockKey(x, y)];
        },

        isBlock: function (x, y) {
            return typeof this.config.blocks[this.genBlockKey(x, y)] != "undefined";
        },

        /**
         * 清除所有障碍点
         */
        clearBlocks: function () {
            this.config.blocks = {};
        },

        genBlockKey: function (x, y) {
            return x + ',' + y;
        },

        /**
         * 生成节点 id 的字符串
         * @param x
         * @param y
         * @returns {string}
         */
        genItemId: function (x, y) {
            return 'id_' + x + '-' + y;
        },

        // 设置选择节点的角色
        setOp: function (op) {
            console.log('op:', op);
            var opsEle = document.getElementsByClassName('ops');
            for (var i = 0; i < opsEle.length; i++) {
                DomUtl.rmCls(opsEle[i], 'active');
            }

            this.curOp = op;
            DomUtl.addCls(opsEle[op - 1], 'active');
        },

        /**
         * 画出路径
         */
        drawPath: function () {
            for (var key in this.result.pathNodes) {
                var point = this.result.pathNodes[key];
                if (this.isStartPoint(point) || this.isEndPoint(point)) {
                    continue;
                }

                var ele = document.getElementById(this.genItemId(point[0], point[1]));
                ele.className = 'item path-item';
            }
        },

        /**
         * 清除路径
         */
        clearPath: function () {
            for (var key in this.result.pathNodes) {
                var point = this.result.pathNodes[key];
                DomUtl.rmCls(document.getElementById(this.genItemId(point[0], point[1])), 'path-item');
                delete this.result.pathNodes[key];
            }

            for (var key in this.result.footprint) {
                var point = this.result.footprint[key];
                DomUtl.rmCls(document.getElementById(this.genItemId(point[0], point[1])), 'path-item-try');
            }
            this.result.footprint = [];
        },

        /**
         * 是否是起点
         * @param point
         * @returns {boolean}
         */
        isStartPoint: function (point) {
            return (point[0] == this.config.startPoint[0]) && (point[1] == this.config.startPoint[1]);
        },

        /**
         * 是否是终点
         * @param point
         * @returns {boolean}
         */
        isEndPoint: function (point) {
            return (point[0] == this.config.endPoint[0]) && (point[1] == this.config.endPoint[1]);
        },

        /**
         * 打印足迹
         */
        drawFootpath: function () {
            if (this.result.footprint.length == 0) {
                return;
            }

            for (var key in this.result.footprint) {
                var point = this.result.footprint[key];
                if (this.isStartPoint(point) || this.isEndPoint(point)) {
                    continue;
                }

                var ele = document.getElementById(this.genItemId(point[0], point[1]));
                ele.className = 'item path-item-try';
            }
        }
    },

    Service: {
        request: function () {
            // 1.1 构建参数
            var blocks = '';
            for (var i in PfPath.Grid.config.blocks) {
                blocks += PfPath.Grid.config.blocks[i].join(',') + ';';
            }

            // 获取 diagonal 的值
            var diagonalRadios = document.getElementsByName('diagonal');
            for (i = 0; i < diagonalRadios.length; i++) {
                if (diagonalRadios[i].checked) {
                    PfPath.Grid.config.diagonal = parseInt(diagonalRadios[i].value) == DIAGONAL_YES;
                }
            }
            var params = {
                start: PfPath.Grid.config.startPoint.join(','),
                end: PfPath.Grid.config.endPoint.join(','),
                x_num: PfPath.Grid.config.xNum,
                y_num: PfPath.Grid.config.yNum,
                x_score: PfPath.Grid.config.xScore,
                y_score: PfPath.Grid.config.yScore,
                diagonal: PfPath.Grid.config.diagonal == true ? DIAGONAL_YES : DIAGONAL_NO,
                blocks: blocks
            };

            // 2.1 HTTP Get 请求
            var http = new XMLHttpRequest();
            http.open('GET', GlobalCfg.api + '?' + StringUtl.objToUrlQuery(params), false);
            http.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            http.send();

            // 3.1 解析响应
            if (http.status != 200) {
                alert('URL错误或服务器出错: ' + http.status);
                return;
            }

            var res = JSON.parse(http.responseText);
            if (res.code != 0) {
                alert('操作失败: ' + res.msg);
                return;
            }

            // 4.1 结果绘图
            if (res.data.path_nodes.length == 0) {
                alert('提示: 无法到达目的地.');
                return;
            }

            PfPath.Grid.result.pathNodes = res.data.path_nodes;
            PfPath.Grid.result.footprint = res.data.footprint;

            PfPath.Grid.drawFootpath();
            PfPath.Grid.drawPath();
        }
    }
};