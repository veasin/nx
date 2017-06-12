# nx is **"a next-gen framework"**
  php 7.0+
  
  使用php语言中的trait实现在代码编写时的加载扩充，而不是运行时的扩充
  
  小框架，可以随意扩充

#feature
- [x] trait,trait,trait!!!
- [x] 支持命名空间
- [x] 支持composer
- [x] 尽可能支持psr
- [x] 尽可能兼容next
- [x] 支持调度 使用route并闭包
  - [x] 同时多route匹配
  - [x] 改造route数组为对象
  - [x] 修改调度方式
- [x] 支持php7，支持php新功能
- [x] 脚手架
  - [x] 项目预制
  - [ ] 编译（打包）

#code
```
class app extends \nx\app{
  use \nx\log\file,
      \nx\control\mvc,
      \nx\router\ca,
      \nx\config\ini,
      \nx\db\pdo;
}
app::factory()->run();
```