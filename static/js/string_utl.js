/**
 * Created by hikoqiu on 2017/6/29.
 */

var StringUtl = {

    /**
     * 简单将 {key: value} 转成 key=value&key=value;
     * @param obj
     * @returns {string}
     */
    objToUrlQuery: function (obj) {
        var list = [];
        for (var key in obj) {
            key = encodeURIComponent(key);
            list.push(key + '=' + obj[key]);
        }

        return list.join('&');
    }
};
