# nx is **"a n x f"**
  php 5.5+ ?

#feature
- [x] trait,trait,trait!!!
- [x] 支持命名空间
- [ ] 支持composer
- [ ] 尽可能支持psr
  - [ ] psr0
  - [ ] psr1
  - [ ] psr2
  - [ ] psr3
  - [ ] psr4
- [ ] 尽可能兼容next
- [ ] 支持调度 使用route并闭包
  - [ ] 同时多route匹配
  - [ ] 改造route数组为对象
  - [ ] 修改调度方式
- [ ] 路由缓存
- [ ] 路由 build(基于工作路径和解析)
- [ ] 支持php7，支持php新功能
- [ ] 脚手架
  - [ ] 项目预制
  - [ ] 数据缓存
  - [ ] 模板编译
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