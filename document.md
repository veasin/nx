# 说明文档

## 流程
 1. 引用 autoload ，可以使用composer或直接引入 \nx\autoload，并注册命名空间对应目录
 2. 创建一个应用如 \demo\app 继承自 \nx\app，并给这个app引入合适的trait
 3. \demo\app::factory()->run()

## 框架说明
 - nx采用大多数框架的结构(如 mvc)作为默认设置，但可以很方便的进行扩充
 - 先是router，然后到controller，调用model后，由view进行输出
 - 所有的常用模块都是由trait构成，由php语法use引入到app或controller或model中
 - 使用trait方式，你完全可以自定义整个框架所有部分，当然，你不能超出框架范围，当然，你也可以来完善并扩充[nx](github.com/urn2/nx)的代码

## 对象说明

### *\nx\app*
整个nx框架的基础，mvc 中的cm部分是扩充自这里的。在这里可以引入需要的trait，包括自定义trait。在初始化的时候，会依照引入顺序初始化对应的trait。
- 默认的方法只有 factory 和 run，其他的扩充需要trait支持
   - *factory()* 只是作为 new 的替代
   - *run()* 开始运行应用
   - getPath() 获取当前工作目录或对应子目录，取决于path设置
   - getUUID() 获取本次app的不重复id
- 默认属性有
  - *ver* 设置大于1.5的时候，默认创建*input*和*output*，否则仅创建*resquest*和*response*作为兼容存在
  - *setup* 配置app的配置，如果你在自己的app里面写了这个会自动读取的，当然，你也可以通过构建的时候把配置传递进来
  - *input* 对象，替代*request*存在，提供对应的请求方法，如 ->body(*'page'*) ->query(*'id'*) ->uri(*'cid'*)等
  - *output* 对象，替代*response*存在，提供统一输出处理，可以注入render自定义数据输出渲染
  - *path* 工作路径，代码的工作路径，一些默认的路径相关处理会使用此属性~~
  - *buffer* 用来缓冲运行时产生的数据，主要是用来做trait之间交互
  - ~~*request* 对象，所有请求的封装~~
  - ~~*response* 对象，本次请求响应，如不处理默认为o2对象~~
- 根据引入不同的trait会附有不同的方法和属性

### *\nx\input*
用来获取应用输入，通过o2实现数组化获取数据，count统计和请求方法函数态获取内容。
- 默认的方法
  - query() http请求的query中的内容，对应地址后面?后的内容
  - body() http请求的body中的内容，会根据请求类型自动做数据转换
  - header() http请求的header中的内容，忽略大小写
  - cookie() http请求中的cookie中的内容
  - uri() http请求的地址，可在某些router工作的时候获取地址中参数
  - method() http请求的方法，可作为判断方式直接使用
  - file() 标准的php文件上传获取方法
  - ip() 获取来源ip的方法
  
### *\nx\output*
用来设置输出的对象，可注入渲染方法来渲染数据的输出。使用的时候直接按照数组方式使用。
- 默认的方法
  - setRender() 设置渲染方法，多次调用覆盖之前设置
- 默认属性
  - buffer 提供临时存储，可被渲染方法调用，不输出

### ~~*\nx\o2*~~ (作废，使用trait /nx/base/o2替代继承)
一个空白的对象，使用了一些php的接口来实现数组访问或序列号或静态化。支持o2['xx']，foreach()，或者直接echo。你可以把这个当作数组来使用，只是在赋值的时候留意小心覆盖。
- 默认的方法
  - set() 覆盖现有数据
  - get() 获取全部数据
  - has() 判断是否存在某个指定的key
  - merge() 把指定的数组合并到当前数据中
  - clear() 清空所有数据
  - count() 此为接口，可以直接使用 count(o2) 这种方式来获取长度
  - __toString() 此为接口，当作为字符串输出的时候，会输出json_encode后的数据

### ~~*\nx\request*~~ (作废，被input替代)
对本次请求进行封装的对象，默认是关联到app上的，但在controller上也可以访问
- 默认的方法
  - method() 当前的请求方式
  - arg() 所有当前请求的参数
  - input() 解析php://input 的内容
  - params() 默认为空，但可以在router中填入对应的数据
  - post() 默认为$_POST 内容
  - get() 默认为 $_GET 内容

### ~~*\nx\mvc\controller*~~ (作废，使用trait /nx/base/callApp替代继承)
mvc模式的控制器，负责操作进入并调用对应的模型，同时输出模型的反馈。按照标准的请求ca两个参数，其中c决定使用哪个控制器，a决定对应的控制器方法，按以下顺序，顺序执行。控制器可引入trait。
1. before() 在所有方法中执行，如果此控制器中有多方法都会执行此方法
2. before[Action] 在动作开始前执行
3. [method][Action] 其中method为本次请求的方式，如get post 或 delete，Action为动作名
4. on[Action] 无论哪种请求方式都会执行此动作
5. after[Action] 在动作开始后执行
6. after 在所有方法执行后执行

### ~~*\nx\mvc\model*~~ (作废，使用trait /nx/base/callApp替代继承)
mvc模式的模型部分，根据不同的参数来反馈不同的业务结果供控制器使用。为了开发和解耦，模型与模型之间不要进行交互。
默认的方法
- instance() 创建模型，并缓存，多次调用会返回同一个模型对象(使用同样的数据包括缓存数据)

### ~~*\nx\mvc\view*~~ (作废，使用output注入渲染方法替代)
mvc模式的视图，只会从控制器中获取数据。继承自o2，当数据中存在“_ _file_ _”时，会根据此指对应的模板文件进行渲染输出。采用的是php本身作为模板语言，可以直接echo模板。
默认的方法
- setFile() 设置此视图的模板
- render() 渲染并返回
- __toString() 返回渲染结果

### *\nx\db\sql*
一个AR对象，指定表名和主键后，会有一些简单方法构建sql，并通过db来执行。需要引入\nx\db\pdo
- factory() 工厂方法
- create() 使用$this->insertSQL方法
- update() 使用$this->executeSQL方法
- read() 使用$this->selectSQL方法
- delete() 使用$this->executeSQL方法
- where[select] 构装函数，设置搜索条件
- join()  构装函数，设置联合查询
- sort()  构装函数，设置排序方式
- limit()  构装函数，设置限制
- group()  构装函数，设置分组

## trait 说明
按照使用情况不同，分为多种，默认只会有一些常用的，可自行扩充。trait会在初始化时候执行默认方法，默认方法为此trait的全名(包含命名空间)。
如 \nx\router\ca 框架会在引入的时候自行判断 nx_router_ca 是否存在，如存在即执行。
你完全可以在自己的项目中创建只属于自己的trait并引入到app中，当然，如果这些比较通用，提交到nx来？

### \nx\router
应用路由，默认只提供同名router方法，会调用$this->control，用来驱动框架开始工作
- router()

#### \nx\router\ca
使用$_GET 中的 c a 并调用 $this->control 方法

#### \nx\router\router
类似klein的路由方式，使用正则匹配请求中的PATH_INFO或QUERY_STRING，执行对应的控制器或回调。可在setup中配置对应规则。
额外提供的方法
- on() 绑定路由和请求方式到回调中，默认绑定全部请求方式
- get() 同上
- post() 同上
- delete() 同上
- put() 同上

### \nx\log
应用日志，提供日志输出方法
- log()

#### \nx\log\dump
直接var_dump

#### \nx\log\header
在响应头中输出输出日志，需要留意的是header长度有限制，并只能显示文本

#### \nx\log\file
输出日志到文件，可在setup中配置输出情况，并有简单的输出模板使用

### \nx\config
应用层面的配置读取方法
- config()

#### \nx\config\files
读取应用所在目录下的config目录下php文件

#### \nx\config\ini
读取app所在目录下的config.ini文件

### \nx\db
数据库访问相关，默认只使用\pdo对象来进行数据库访问

#### \nx\db\pdo
- db() 根据setup中的配置，返回一个\pdo对象
- insertSQL() 执行一条sql语句，并返回对应的插入id
- selectSQL() 执行一条sql语句，并返回对应数据，数组格式
- executeSQL() 执行一条sql语句，并返回影响数目

#### \nx\db\table
- table() 返回一个\nx\db\sql 对象

### \nx\cache

#### \nx\cache\memcache
返回一个 \memcache 对象，读取setup设置

#### \nx\cache\mongo
返回一个\MongoClient对象，读取setup设置

#### \nx\cache\redis
返回一个\redis对象，读取setup设置

### \nx\response
处理请求的响应，默认会使用o2作为数据的响应，可自定义，提供
- view() 方法

#### \nx\response\view
返回一个 \nx\mvc\view 对象，默认模板所在目录为app目录下的views目录

##其他 helpers
框架会提供一些额外的对象来辅助开发，使用共同的命名空间 \nx\helpers，可以直接调用或访问

### \nx\helpers\curl
提供对curl封装

### \nx\helpers\file
提供对文件系统处理的封装

## 其他
待续……