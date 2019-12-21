接口调用说明
===================

#1. 调用说明
- 开发环境域名：http://api.xxxx.com
- 生产环境域名：http://api.xxxx.com


#2. 授权方式：暂无

#3. 加密方式：暂无

#4. 接口访问示例（依照具体接口参数为准）：
   - http://api.xxxx.com/v1/goods-category/view?id=1
   - http://api.xxxx.com/v1/goods-category/view?id=1&expand=items

   - http://api.xxxx.com/v1/goods-category/index
   - http://api.xxxx.com/v1/goods-category/index?filter[category_name]=xxxx
   - http://api.xxxx.com/v1/goods-category/index?filter[category_name]=xxxx&expand=items
   
   
#5. 分页
##5.1 当前数据的分页信息（分页信息统一放置在header中）
    - X-Pagination-Total-Count：搜索到的记录总数量
    - X-Pagination-Page-Count：总页数
    - X-Pagination-Current-Page：当前页码
    - X-Pagination-Per-Page：每页显示的数量
    
##5.2 翻页
    - 对于每个获取列表的接口，url上带个page参数即可，如果想控制每一页显示的数量，可以传per-page参数。比如：
    http://api.xxxx.com/v1/goods-category/index?page=1
    http://api.xxxx.com/v1/goods-category/index?page=2
    http://api.xxxx.com/v1/goods-category/index?page=1&per-page=10
    
##5.3 排序
    对于每个获取列表的接口，url上带个sort参数即可，比如
    - 按照id正序排列：http://api.xxxx.com/v1/goods-category/index?sort=goods_category_id
    - 按照id倒叙排列：http://api.xxxx.com/v1/goods-category/index?sort=-goods_category_id
    



#6. 接口的认证方式通过在header中存放access-token的方式。
    方法：将以下键值对放到需要认证权限的api请求的header中
```
键：Access-Token
值：Bearer 111111
注意：值的存储方式是：Bearer+空格+token值，开发阶段暂时用111111这个固定的token就可以
```


#7. 接口返回值 

```json
{
    "code": 200,
    "isOk": true,
    "msg": "OK",
    "data": null
```
##7.1 code字段可选值：
- 200: OK。一切正常。
- 201: 响应 POST 请求时成功创建一个资源。Location header 包含的URL指向新创建的资源。
- 204: 该请求被成功处理，响应不包含正文内容 (类似 DELETE 请求)。
- 304: 资源没有被修改。可以使用缓存的版本。
- 400: 错误的请求。可能通过用户方面的多种原因引起的，例如在请求体内有无效的JSON 数据，无效的操作参数，等等。
- 401: 用户认证失败。一般是由于token失效引起。
- 403: 已经经过身份验证的用户不允许访问指定的 API 末端。
- 404: 所请求的资源不存在。
- 405: 不被允许的方法。 请检查 Allow header 允许的HTTP方法。
- 415: 不支持的媒体类型。 所请求的内容类型或版本号是无效的。
- 422: 数据验证失败 (例如，响应一个 POST 请求)。 请检查响应体内详细的错误消息。
- 429: 请求过多。 由于限速请求被拒绝。
- 500: 内部服务器错误。 这可能是由于内部程序错误引起的。


##7.2 isOk
布尔类型，标记该接口是否处理成功

##7.3 msg
string类型，错误信息

##7.4 data
混合数据类型，当返回一个对象的时候，data是对象类型
当请求一个列表数据的时候，data是一个数组


#8. 关于接口返回关联对象的使用方法（以活动举例）
- http://api.xxxx.com/v1/activity/index   //获取活动列表 
- http://api.xxxx.com/v1/activity/index?expand=activityGoods  //获取活动列表，并且把"活动商品关联关系对象"也获取 
- http://api.xxxx.com/v1/activity/index?expand=activityGoods.goods  //获取活动列表，并且把"活动商品关联关系对象"也获取，同时也把activityGoods下的goods也获取
-- 注意： expand=activityGoods.goods这样的写法会同时把activityGoods和goods都获取，并且理论上，本系统所有接口都支持无限层级的关联对象获取，但是考虑到复杂程度和查询效率，并不建议这样做。