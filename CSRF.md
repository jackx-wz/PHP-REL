## Cross Site Request Forgery 跨站域请求伪造

> CSRF 是攻击者在受害者还不知情的情况下，伪造受害者的请求发送给被攻击的站点。
黑客攻击主要是透过受害者的cookie骗取服务器的信任，但他又直接拿不到cookie，浏览器又有同源策略，所以黑客也解析不了其他网站的返回结果。
所以黑客只能给被攻击网站发送请求，希望执行请求中的命令。如果执行成功，那么服务器的数据将被改变。所以防范也主要是防范会修改数据的请求。

## 有可能用到的防御手段
1. 验证http referer
如果是其他网站提交的请求，直接判断referer。referer在现代的主流浏览器基本都不可改。但对于旧的浏览器还是有风险。还有referer如果被用户禁用了，那么会影响到他们正常的使用。
2. 请求地址中添加token
服务器添加一个token放在session中，前端在请求的地址中加一个参数 csrftoken。
但如果是开放性的论坛网站，黑客可以发布自己网站的网址，利用referer拿到token，然后马上进行CSRF攻击。
3. HTTP头中设置属性验证


## laravel中的使用
* laravel会自动在头的meta中加载一个 csrf_token
* 在所有ajax中会设置header  X-CSRF-TOKEN
* 为了兼容一些js框架，会写入_token到cookie，JS框架会自动设置到ajax头中为 X-XSRF-TOKEN

## 后端比较的顺序：
1. 表单中的 _token
2. Ajax头中的 X-CSRF-TOKEN
3. Cookie中的 X-XSRF-TOKEN
