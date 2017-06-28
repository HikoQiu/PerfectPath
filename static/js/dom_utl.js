/**
 * Created by hikoqiu on 2017/6/28.
 */

var DomUtl = {
    /**
     * 是否存在某 class
     * @param ele
     * @param cls
     * @returns {Array|{index: number, input: string}}
     */
    hasCls: function (ele, cls) {
        clsList = ele.className.split(/\s+/);
        for (var x in  clsList) {
            if (clsList[x] == cls) {
                return true;
            }
        }
        return false;
    },

    /**
     * 添加一个 class
     * @param ele
     * @param cls
     */
    addCls: function (ele, cls) {
        if (this.hasCls(ele, cls)) {
            return;
        }
        ele.className += " " + cls;
    },

    /**
     * 删除 class
     * @param ele
     * @param cls
     */
    rmCls: function (ele, cls) {
        ele.className = ele.className.replace(/(\s+)/gi, ' ');
        console.log(cls, ele.className);
        ele.className = ele.className.replace(cls, '');
        console.log(ele.className);
    }
};
