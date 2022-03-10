<?php
/**
 * Created by PhpStorm.
 * User: Vea
 * Date: 2019/04/17 017
 * Time: 11:55
 */
declare(strict_types=1);
namespace nx\helpers\db;

/**
 * Class sql 单表模式，移除多表逻辑
 * MySQL 8.0 https://dev.mysql.com/doc/refman/8.0/en/
 * @package nx\helpers\db
 *
 * 12.2
 * @method static sql\part operate($N1, $N2, $operator='=')
 * @method static sql\part equal($N1, $N2) =
 * @method static sql\part between($expr,$min,$max,$NOT=false) expr BETWEEN min AND max 假如expr大于或等于 min 且expr 小于或等于max, 则BETWEEN 的返回值为1,或是0。若所有参数都是同一类型，则上述关系相当于表达式   (min <= expr AND expr <= max)。其它类型的转换根据本章开篇所述规律进行，且适用于3种参数中任意一种。
 *
 *	AND, &&			Logical AND
 *	=				Assign a value (as part of a SET statement, or as part of the SET clause in an UPDATE statement)
 *	:=				Assign a value
 *? BETWEEN	... AND ...	Check whether a value is within a range of values
 *	BINARY				Cast a string to a binary string
 *	&				Bitwise AND
 *	~				Bitwise inversion
 *	|				Bitwise OR
 *	^				Bitwise XOR
 *	CASE			Case operator
 *	DIV				Integer division
 *	/				Division operator
 *	=				Equal operator
 *	<=>				NULL-safe equal to operator
 *	>				Greater than operator
 *	>=				Greater than or equal operator
 *	IS				Test a value against a boolean //operate(1 , false, 'is')
 *	IS NOT			Test a value against a boolean //operate(1 , false, 'is not')
 *	IS NOT NULL		NOT NULL value test //operate(1 , null, 'is not')
 *	IS NULL	NULL 	value test
 *	->				Return value from JSON column after evaluating path; equivalent to JSON_EXTRACT().
 *	->>				Return value from JSON column after evaluating path and unquoting the result; equivalent to JSON_UNQUOTE(JSON_EXTRACT()).
 *	<<				Left shift
 *	<				Less than operator
 *	<=				Less than or equal operator
 *	LIKE			Simple pattern matching
 *	-				Minus operator
 *	%, MOD			Modulo operator
 *	NOT, !			Negates value
 *	NOT 			BETWEEN ... AND ...	Check whether a value is not within a range of values
 *	!=, <>			Not equal operator
 *	NOT 			LIKE	Negation of simple pattern matching
 *	NOT 			REGEXP	Negation of REGEXP
 *	||, OR			Logical OR
 *	+				Addition operator
 *	REGEXP			Whether string matches regular expression
 *	>>				Right shift
 *	RLIKE			Whether string matches regular expression
 *	SOUNDS 			LIKE	Compare sounds
 *	*				Multiplication operator
 *	-				Change the sign of the argument
 *	XOR				Logical XOR
 *
 * 12.3.2 Comparison Functions and Operators 比较函数和操作符
 *	COALESCE()	Return the first non-NULL argument
 *	GREATEST()	Return the largest argument
 *	INTERVAL()	Return the index of the argument that is less than the first argument
 *	ISNULL()	Test whether the argument is NULL
 *	LEAST()		Return the smallest argument
 *	STRCMP()	Compare two strings
 *
 * @method static sql\part COALESCE(...$values) Returns the first non-NULL value in the list, or NULL if there are no non-NULL values.
 * @method static sql\part GREATEST(...$values) With two or more arguments, returns the largest (maximum-valued) argument. The arguments are compared using the same rules as for LEAST().
 * @method static sql\part in($expr, ...$values) expr IN (value,...) 若expr 为IN列表中的任意一个值，则其返回值为 1 , 否则返回值为0。假如所有的值都是常数，则其计算和分类根据 expr 的类型进行。这时，使用二分搜索来搜索信息。如IN值列表全部由常数组成，则意味着IN 的速度非常之快。如expr 是一个区分大小写的字符串表达式，则字符串比较也按照区分大小写的方式进行
 *?@method static sql\part notIN($expr, ...$values) expr NOT IN (value,...)
 * @method static sql\part ISNULL($expr) If expr is NULL, ISNULL() returns 1, otherwise it returns 0.
 * @method static sql\part INTERVAL(...$values) 假如N < N1，则返回值为0；假如N < N2 等等，则返回值为1；假如N 为NULL，则返回值为 -1 。所有的参数均按照整数处理。为了这个函数的正确运行，必须满足 N1 < N2 < N3 < ……< Nn 。其原因是使用了二分查找(极快速)
 * @method static sql\part LEAST(...$values) 在有两个或多个参数的情况下， 返回值为最小 (最小值) 参数。用一下规则将自变量进行对比
 *
 * 12.3.3 Logical Operators
 *	AND, &&		Logical AND
 *	NOT, !		Negates value
 *	OR, ||		Logical OR
 *	XOR			Logical XOR
 * @method static sql\part not($expr) NOT ! 逻辑 NOT。当操作数为0 时，所得值为 1 ；当操作数为非零值时，所得值为  0 ，而当操作数为NOT NULL时，所得的返回值为 NULL
 * @method static sql\part and($expr1, $expr2) AND && 逻辑AND。当所有操作数均为非零值、并且不为NULL时，计算所得结果为  1 ，当一个或多个操作数为0 时，所得结果为 0 ，其余情况返回值为 NULL
 * @method static sql\part or($expr1, $expr2) OR || 逻辑 OR。当两个操作数均为非 NULL值时，如有任意一个操作数为非零值，则结果为1，否则结果为0。当有一个操作数为NULL时，如另一个操作数为非零值，则结果为1，否则结果为 NULL 。假如两个操作数均为  NULL，则所得结果为 NULL
 * @method static sql\part xor($expr1, $expr2) XOR 逻辑XOR。当任意一个操作数为 NULL时，返回值为NULL。对于非   NULL 的操作数，假如一个奇数操作数为非零值，则计算所得结果为  1 ，否则为  0
 *
 * 12.3.3 Logical Operators
 *	=			Assign a value (as part of a SET statement, or as part of the SET clause in an UPDATE statement)
 *	:=			Assign a value
 * @method static sql\part assign($var, $value) SELECT @var1 := 1
 *
 * 12.4 Control Flow Functions
 *	CASE		Case operator
 *	IF()		If/else construct
 *	IFNULL()    Null if/else construct
 *	NULLIF()	Return NULL if expr1 = expr2
 * @method static sql\part IFIF($expr1,$expr2,$expr3) 如果 expr1 是TRUE (expr1 <> 0 and expr1 <> NULL)，则 IF()的返回值为expr2; 否则返回值则为 expr3。IF() 的返回值为数字值或字符串值，具体情况视其所在语境而定
 * @method static sql\part IFNULL($expr1,$expr2) 假如expr1 不为 NULL，则 IFNULL() 的返回值为 expr1; 否则其返回值为 expr2。IFNULL()的返回值是数字或是字符串，具体情况取决于其所使用的语境
 * @method static sql\part NULLIF($expr1,$expr2) 如果expr1 = expr2  成立，那么返回值为NULL，否则返回值为 expr1。这和CASE WHEN expr1 = expr2 THEN NULL ELSE expr1 END相同
 *
 * 12.5 String Functions
 * @method static sql\part ASCII($str) 返回第1字节的ascii码值
 * @method static sql\part BIN($number) N的二进制值的字符串表示
 * @method static sql\part BIT_LENGTH($str) 返回值为二进制的字符串str 长度
 * @method static sql\part CHAR(...$ascii) CHAR()将每个参数N理解为一个整数，其返回值为一个包含这些整数的代码值所给出的字符的字符串。NULL值被省略。大于 255的CHAR()参数被转换为多结果字符。 例如，CHAR(256) 相当于 CHAR(1,0), 而CHAR(256*256) 则相当于 CHAR(1,0,0)：
 * @method static sql\part CHAR_LENGTH($string) 返回值为字符串str 的长度，长度的单位为字符。一个多字节字符算作一个单字符。对于一个包含五个二字节字符集, LENGTH()返回值为 10, 而CHAR_LENGTH()的返回值为5。
 * @method static sql\part CHARACTER_LENGTH($string) 同CHAR_LENGTH
 * @method static sql\part CONCAT(...$strings) 返回结果为连接参数产生的字符串。如有任何一个参数为NULL ，则返回值为 NULL。或许有一个或多个参数。 如果所有参数均为非二进制字符串，则结果为非二进制字符串。 如果自变量中含有任一二进制字符串，则结果为一个二进制字符串。一个数字参数被转化为与之相等的二进制字符串格式；若要避免这种情况，可使用显式类型 cast, 例如： SELECT CONCAT(CAST(int_col AS CHAR), char_col)
 * @method static sql\part CONCAT_WS($separator, ...$strings) CONCAT_WS() 代表 CONCAT With Separator ，是CONCAT()的特殊形式。   第一个参数是其它参数的分隔符。分隔符的位置放在要连接的两个字符串之间。分隔符可以是一个字符串，也可以是其它参数。如果分隔符为 NULL，则结果为 NULL。函数会忽略任何分隔符参数后的 NULL 值。
 * @method static sql\part ELT($number, ...$strings) 若N = 1，则返回值为  str1 ，若N = 2，则返回值为 str2 ，以此类推。   若N 小于1或大于参数的数目，则返回值为 NULL 。 ELT() 是  FIELD()的补数
 * @method static sql\part EXPORT_SET($bits,$on,$off,$separator,$number_of_bits) 返回值为一个字符串，其中对于bits值中的每个位组，可以得到一个 on 字符串，而对于每个清零比特位，可以得到一个off 字符串。bits 中的比特值按照从右到左的顺序接受检验 (由低位比特到高位比特)。字符串被分隔字符串分开(默认为逗号‘,’)，按照从左到右的顺序被添加到结果中
 * @method static sql\part FIELD($find, ...$strings) 返回值为str1, str2, str3,……列表中的str 指数。在找不到str 的情况下，返回值为 0
 * @method static sql\part FIND_IN_SET($find, $strings) 假如字符串str 在由N 子链组成的字符串列表strlist 中， 则返回值的范围在 1 到 N 之间 。一个字符串列表就是一个由一些被‘,’符号分开的自链组成的字符串。如果第一个参数是一个常数字符串，而第二个是type SET列，则   FIND_IN_SET() 函数被优化，使用比特计算。如果str不在strlist 或strlist 为空字符串，则返回值为
 * @method static sql\part FORMAT($x, $d, $locale=null) 将number X设置为格式 '#,###,###.##', 以四舍五入的方式保留到小数点后D位, 而返回结果为一个字符串
 * @method static sql\part FROM_BASE64($str) decode base64
 * @method static sql\part HEX($N_or_S) 如果N_OR_S 是一个数字，则返回一个 十六进制值 N 的字符串表示，在这里，   N 是一个longlong (BIGINT)数。这相当于 CONV(N,10,16)。如果N_OR_S 是一个字符串，则返回值为一个N_OR_S的十六进制字符串表示， 其中每个N_OR_S 里的每个字符被转化为两个十六进制数字。
 * @method static sql\part INSERT($str,$pos,$len,$newstr) 返回字符串 str, 其子字符串起始于 pos 位置和长期被字符串 newstr取代的len 字符。  如果pos 超过字符串长度，则返回值为原始字符串。 假如len的长度大于其它字符串的长度，则从位置pos开始替换。若任何一个参数为null，则返回值为NULL。
 * @method static sql\part INSTR($str,$substr) 返回字符串 str 中子字符串的第一个出现位置。这和LOCATE()的双参数形式相同，除非参数的顺序被颠倒
 * @method static sql\part LCASE($str) synonym for LOWER().
 * @method static sql\part LEFT($str,$len) 返回从字符串str 开始的len 最左字符
 * @method static sql\part LENGTH($str) 返回值为字符串str 的长度，单位为字节。一个多字节字符算作多字节。这意味着 对于一个包含5个2字节字符的字符串， LENGTH() 的返回值为 10, 而 CHAR_LENGTH()的返回值则为5
 * @method static sql\part LOAD_FILE($file_name) Reads the file and returns the file contents as a string.
 * @method static sql\part LOCATE($substr, $str, $pos=null) The first syntax returns the position of the first occurrence of substring substr in string str.
 * @method static sql\part LOWER($str) 返回字符串 str 以及所有根据最新的字符集映射表变为小写字母的字符 (默认为  cp1252 Latin1)。
 * @method static sql\part LPAD($str,$len,$padstr) 返回字符串 str, 其左边由字符串padstr 填补到len 字符长度。假如str 的长度大于len, 则返回值被缩短至 len 字符。
 * @method static sql\part LTRIM($str) 返回字符串 str ，其引导空格字符被删除
 * @method static sql\part MAKE_SET($bits, ...$strings) 返回一个设定值 (一个包含被‘,’号分开的字字符串的字符串) ，由在bits 组中具有相应的比特的字符串组成。str1 对应比特 0, str2 对应比特1,以此类推。str1, str2, ...中的 NULL值不会被添加到结果中。
 * @method static sql\part MID($str,$pos, $len=null) synonym for SUBSTRING(str,pos,len).
 * @method static sql\part OCT($N) 返回一个 N的八进制值的字符串表示，其中 N 是一个longlong (BIGINT)数。这等同于CONV(N,10,8)。若N 为 NULL ，则返回值为NULL
 * @method static sql\part OCTET_LENGTH($str) synonym for LENGTH().
 * @method static sql\part ORD($str) 若字符串str 的最左字符是一个多字节字符，则返回该字符的代码， 代码的计算通过使用以下公式计算其组成字节的数值而得出:  假如最左字符不是一个多字节字符，那么 ORD()和函数ASCII()返回相同的值
 * @method static sql\part POSITION($substr, $str) synonym for LOCATE(substr,str).
 * @method static sql\part QUOTE($str) 引证一个字符串，由此产生一个在SQL语句中可用作完全转义数据值的结果。  返回的字符串由单引号标注，每例都带有单引号 (‘'’)、 反斜线符号 (‘\’)、 ASCII NUL以及前面有反斜线符号的Control-Z 。如果自变量的值为NULL, 则返回不带单引号的单词 “NULL”
 * @method static sql\part REPEAT($str,$count) 返回一个由重复的字符串str 组成的字符串，字符串str的数目等于count 。 若 count <= 0,则返回一个空字符串。若str 或 count 为 NULL，则返回 NULL
 * @method static sql\part REPLACE($str,$from_str,$to_str) 返回字符串str 以及所有被字符串to_str替代的字符串from_str
 * @method static sql\part REVERSE($str) 返回字符串 str ，顺序和字符顺序相反
 * @method static sql\part RIGHT($str,$len) 从字符串str 开始，返回最右len 字符
 * @method static sql\part RPAD($str,$len,$padstr) 返回字符串str, 其右边被字符串 padstr填补至len 字符长度。假如字符串str 的长度大于 len,则返回值被缩短到与 len 字符相同长度
 * @method static sql\part RTRIM($str) 返回字符串 str ，结尾空格字符被删去
 * @method static sql\part SOUNDEX($str) 从str返回一个soundex字符串。 两个具有几乎同样探测的字符串应该具有同样的 soundex 字符串。一个标准的soundex 字符串的长度为4个字符，然而SOUNDEX() 函数会返回一个人以长度的字符串。 可使用结果中的SUBSTRING() 来得到一个标准 soundex 字符串。在str中，会忽略所有未按照字母顺序排列的字符。 所有不在A-Z范围之内的国际字母符号被视为元音字母
 * @method static sql\part SPACE($N) 返回一个由N 间隔符号组成的字符串
 * @method static sql\part SUBSTRING($str,$pos, $len=null) 不带有len 参数的格式从字符串str返回一个子字符串，起始于位置 pos。带有len参数的格式从字符串str返回一个长度同len字符相同的子字符串，起始于位置 pos。 使用 FROM的格式为标准 SQL 语法。也可能对pos使用一个负值。假若这样，则子字符串的位置起始于字符串结尾的pos 字符，而不是字符串的开头位置。在以下格式的函数中可以对pos 使用一个负值。
 * @method static sql\part SUBSTRING_INDEX($str,$delim,$count) 在定界符 delim 以及count 出现前，从字符串str返回自字符串。若count为正值,则返回最终定界符(从左边开始)左边的一切内容。若count为负值，则返回定界符（从右边开始）右边的一切内容
 * @method static sql\part TO_BASE64($str) Converts the string argument to base-64 encoded form and returns the result as a character string with the connection character set and collation.
 * @method static sql\part TRIM($str,$rem='', $rule='both') 返回字符串 str ， 其中所有remstr 前缀和/或后缀都已被删除。若分类符BOTH、LEADIN或TRAILING中没有一个是给定的,则假设为BOTH 。 remstr 为可选项，在未指定情况下，可删除空格
 * @method static sql\part UCASE($str) synonym for UPPER().
 * @method static sql\part UNHEX($str) 执行从HEX(str)的反向操作。就是说，它将参数中的每一对十六进制数字理解为一个数字，并将其转化为该数字代表的字符。结果字符以二进制字符串的形式返回
 * @method static sql\part UPPER($str) 返回字符串str， 以及根据最新字符集映射转化为大写字母的字符 (默认为cp1252 Latin1)
 * @method static sql\part WEIGHT_STRING($str, $N, $type='char') This function returns the weight string for the input string. WEIGHT_STRING('ab' AS CHAR(4))
 *
 * 12.5.1 String Comparison Functions
 * LIKE			Simple pattern matching
 * NOT LIKE		Negation of simple pattern matching
 * STRCMP()		Compare two strings
 * like($expr,$pat,$ESCAP=null) expr LIKE pat [ESCAPE 'escape-char'] 模式匹配，使用SQL简单正规表达式比较。返回1 (TRUE) 或 0 (FALSE)。 若 expr 或 pat 中任何一个为 NULL,则结果为 NULL
 * noLike($expr,$pat,$ESCAP=null) 模式匹配，使用SQL简单正规表达式比较。返回1 (TRUE) 或 0 (FALSE)。 若 expr 或 pat 中任何一个为 NULL,则结果为 NULL
 * @method static sql\part STRCMP($expr1,$expr2) 若所有的字符串均相同，则返回STRCMP()，若根据当前分类次序，第一个参数小于第二个，则返回  -1，其它情况返回 1
 *
 * 12.5.2 Regular Expressions
 * NOT REGEXP		Negation of REGEXP
 * REGEXP			Whether string matches regular expression
 * REGEXP_INSTR()	Starting index of substring matching regular expression
 * REGEXP_LIKE()	Whether string matches regular expression
 * REGEXP_REPLACE()	Replace substrings matching regular expression
 * REGEXP_SUBSTR()	Return substring matching regular expression
 * RLIKE			Whether string matches regular expression
 * @method static sql\part REGEXP_INSTR($expr, $pat, $pos=1, $occurrence=1, $return_option=0, $match_type=null)
 * @method static sql\part REGEXP_LIKE($expr, $pat, $match_type=null)
 * @method static sql\part REGEXP_REPLACE($expr, $pat, $repl, $pos=1, $occurrence=0, $match_type=null)
 * @method static sql\part REGEXP_SUBSTR($expr, $pat, $pos=1, $occurrence=1, $match_type=null)
 *
 * 12.6.1 Arithmetic Operators
 * DIV		Integer division
 * /		Division operator
 * -		Minus operator
 * %, MOD	Modulo operator
 * +		Addition operator
 * *		Multiplication operator
 * -		Change the sign of the argument
 *
 * 12.6.2 Mathematical Functions
 * @method static sql\part ABS($X) 返回X 的绝对值
 * @method static sql\part ACOS($X) 返回X 反余弦, 即, 余弦是X的值。若X 不在-1到 1的范围之内，则返回 NULL
 * @method static sql\part ASIN($X) 返回X 的反正弦，即，正弦为X 的值。若X  若X 不在-1到 1的范围之内，则返回 NULL
 * @method static sql\part ATAN($X, $Y=null) 返回X 的反正切，即，正切为X 的值
 * @method static sql\part ATAN2($Y, $X) 返回两个变量X 及Y的反正切。 它类似于 Y 或 X的反正切计算,  除非两个参数的符号均用于确定结果所在象限
 * @method static sql\part CEIL($X) synonym for CEILING().
 * @method static sql\part CEILING($X) 返回不小于X 的最小整数值
 * @method static sql\part CONV($N,$from_base,$to_base) 不同数基间转换数字。返回值为数字的N字符串表示，由from_base基转化为 to_base 基。如有任意一个参数为NULL，则返回值为 NULL。自变量 N 被理解为一个整数，但是可以被指定为一个整数或字符串。最小基数为 2 ，而最大基数则为 36。 If to_base 是一个负数，则 N 被看作一个带符号数。否则， N 被看作无符号数。 CONV() 的运行精确度为 64比特
 * @method static sql\part COS($X) 返回X 的余弦，其中X在弧度上已知
 * @method static sql\part COT($X) 返回X 的余切
 * @method static sql\part CRC32($expr) 计算循环冗余码校验值并返回一个 32比特无符号值。若参数为NULL ，则结果为 NULL。该参数应为一个字符串，而且在不是字符串的情况下会被作为字符串处理（若有可能）
 * @method static sql\part DEGREES($X) 返回参数 X, 该参数由弧度被转化为度
 * @method static sql\part EXP($X) 返回e的X乘方后的值(自然对数的底)
 * @method static sql\part FLOOR($X) 返回不大于X的最大整数值
 * FORMAT($X,$D) 将数字X 的格式写成'#,###,###.##'格式, 即保留小数点后 D位，而第D位的保留方式为四舍五入，然后将结果以字符串的形式返回。详见12.9.4节，“其他函数”
 * HEX($N_or_S) 如果N_OR_S 是一个数字，则返回一个 十六进制值 N 的字符串表示，在这里，   N 是一个longlong (BIGINT)数。这相当于 CONV(N,10,16)。如果N_OR_S 是一个字符串，则返回值为一个N_OR_S的十六进制字符串表示， 其中每个N_OR_S 里的每个字符被转化为两个十六进制数字。
 * @method static sql\part LN($X) 返回X 的自然对数,即, X 相对于基数e 的对数
 * @method static sql\part LOG($X, $whenBisX=null) 若用一个参数调用，这个函数就会返回X 的自然对数
 * @method static sql\part LOG2($X) 返回X 的基数为2的对数.对于查出存储一个数字需要多少个比特，LOG2()非常有效。这个函数相当于表达式 LOG(X) / LOG(2)
 * @method static sql\part LOG10($X) 返回X的基数为10的对数
 * @method static sql\part MOD($N,$M) N % M N MOD M 模操作。返回N 被 M除后的余数
 * @method static sql\part PI() 返回 ϖ (pi)的值。默认的显示小数位数是7位,然而 MySQL内部会使用完全双精度值
 * @method static sql\part POW($X,$Y) 返回X 的Y乘方的结果值
 * @method static sql\part RADIANS($X) 返回由度转化为弧度的参数 X,  (注意 ϖ 弧度等于180度）
 * @method static sql\part RAND($N=null) 返回一个随机浮点值 v ，范围在 0 到1 之间 (即, 其范围为 0 ≤ v ≤ 1.0)。若已指定一个整数参数 N ，则它被用作种子值，用来产生重复序列
 * @method static sql\part ROUND($X,$D=null) 返回参数X, 其值接近于最近似的整数。在有两个参数的情况下，返回 X ，其值保留到小数点后D位，而第D位的保留方式为四舍五入。若要接保留X值小数点左边的D 位，可将 D 设为负值
 * @method static sql\part SIGN($X) 返回参数作为-1、 0或1的符号，该符号取决于X 的值为负、零或正
 * @method static sql\part SIN($X) 返回X 正弦，其中 X 在弧度中被给定
 * @method static sql\part SQRT($X) 返回非负数X 的二次方根
 * @method static sql\part TAN($X) 返回X 的正切，其中X 在弧度中被给定
 * @method static sql\part TRUNCATE($X,$D=null) 返回被舍去至小数点后D位的数字X。若D 的值为 0, 则结果不带有小数点或不带有小数部分。可以将D设为负数,若要截去(归零) X小数点左起第D位开始后面所有低位的值
 *
 * 12.7 Date and Time Functions
 * @method static sql\part ADDDATE($date) 当被第二个参数的INTERVAL格式激活后， ADDDATE()就是DATE_ADD()的同义词。相关函数SUBDATE() 则是DATE_SUB()的同义词。对于INTERVAL参数上的信息 ，请参见关于DATE_ADD()的论述
 * @method static sql\part ADDTIME($expr,$expr2) ADDTIME()将 expr2添加至expr 然后返回结果。 expr 是一个时间或时间日期表达式，而expr2 是一个时间表达式
 * @method static sql\part CONVERT_TZ($dt,$from_tz,$to_tz) 将时间日期值dt 从from_tz 给出的时区转到to_tz给出的时区，然后返回结果值
 * @method static sql\part CURDATE() 将当前日期按照'YYYY-MM-DD' 或YYYYMMDD 格式的值返回，具体格式根据函数用在字符串或是数字语境中而定
 * @method static sql\part CURRENT_DATE() synonyms for CURDATE().
 * @method static sql\part CURRENT_TIME() synonyms for CURTIME().
 * @method static sql\part CURRENT_TIMESTAMP() synonyms for NOW().
 * @method static sql\part CURTIME() 将当前时间以'HH:MM:SS'或 HHMMSS 的格式返回， 具体格式根据函数用在字符串或是数字语境中而定
 * @method static sql\part DATE($expr) 提取日期或时间日期表达式expr中的日期部分
 * @method static sql\part DATEDIFF($expr,$expr2) 返回起始时间 expr和结束时间expr2之间的天数。Expr和expr2 为日期或 date-and-time 表达式。计算中只用到这些值的日期部分
 * @method static sql\part DATE_ADD($date, $expr, $unit) 这些函数执行日期运算。 date 是一个 DATETIME 或DATE值，用来指定起始时间。 expr 是一个表达式，用来指定从起始日期添加或减去的时间间隔值。  Expr是一个字符串;对于负值的时间间隔，它可以以一个 ‘-’开头。 type 为关键词，它指示了表达式被解释的方式
 * @method static sql\part DATE_FORMAT($date,$format) 根据format 字符串安排date 值的格式
 * @method static sql\part DATE_SUB($date, $expr, $unit) See the description for DATE_ADD().
 * @method static sql\part DAY($date) synonym for DAYOFMONTH().
 * @method static sql\part DAYNAME($date) 返回date 对应的工作日名称
 * @method static sql\part DAYOFMONTH($date) 返回date 对应的该月日期，范围是从 1到31
 * @method static sql\part DAYOFWEEK($date) 返回date (1 = 周日, 2 = 周一, ..., 7 = 周六)对应的工作日索引。这些索引值符合 ODBC标准
 * @method static sql\part DAYOFYEAR($date) 返回date 对应的一年中的天数，范围是从 1到366
 * @method static sql\part EXTRACT($type,$date) type FROM date 函数所使用的时间间隔类型说明符同 DATE_ADD()或DATE_SUB()的相同,但它从日期中提取其部分，而不是执行日期运算
 * @method static sql\part FROM_DAYS($N) 给定一个天数  N, 返回一个DATE值
 * @method static sql\part FROM_UNIXTIME($unix_timestamp,$format) 返回'YYYY-MM-DD HH:MM:SS'或YYYYMMDDHHMMSS 格式值的unix_timestamp参数表示，具体格式取决于该函数是否用在字符串中或是数字语境中
 * @method static sql\part GET_FORMAT($datetime, $local=null) 'EUR'|'USA'|'JIS'|'ISO'|'INTERNAL' 返回一个格式字符串。这个函数在同DATE_FORMAT 及STR_TO_DATE()函数结合时很有用
 * @method static sql\part HOUR($time) 返回time 对应的小时数。对于日时值的返回值范围是从 0 到 23
 * @method static sql\part LAST_DAY($date) 获取一个日期或日期时间值，返回该月最后一天对应的值。若参数无效，则返回NULL
 * @method static sql\part LOCALTIME() synonyms for NOW().
 * @method static sql\part LOCALTIMESTAMP() synonyms for NOW().
 * @method static sql\part MAKEDATE($year,$dayofyear) 给出年份值和一年中的天数值，返回一个日期。dayofyear 必须大于 0 ，否则结果为 NULL
 * @method static sql\part MAKETIME($hour,$minute,$second) 返回由hour、 minute和second 参数计算得出的时间值
 * @method static sql\part MICROSECOND($expr) 从时间或日期时间表达式expr返回微秒值，其数字范围从 0到 999999
 * @method static sql\part MINUTE($time) 返回 time 对应的分钟数,范围是从 0 到 59
 * @method static sql\part MONTH($date) 返回date 对应的月份，范围时从 1 到 12
 * @method static sql\part MONTHNAME($date) 返回date 对应月份的全名
 * @method static sql\part NOW() 返回当前日期和时间值，其格式为 'YYYY-MM-DD HH:MM:SS' 或YYYYMMDDHHMMSS ， 具体格式取决于该函数是否用在字符串中或数字语境中
 * @method static sql\part PERIOD_ADD($P,$N) 添加 N 个月至周期P (格式为YYMM 或YYYYMM)，返回值的格式为 YYYYMM。注意周期参数 P 不是日期值
 * @method static sql\part PERIOD_DIFF($P1,$P2) 返回周期P1和 P2 之间的月份数。P1 和P2 的格式应该为YYMM或YYYYMM。注意周期参数 P1和P2 不是日期值
 * @method static sql\part QUARTER($date) 返回date 对应的一年中的季度值，范围是从 1到 4
 * @method static sql\part SECOND($time) 返回time 对应的秒数, 范围是从 0到59
 * @method static sql\part SEC_TO_TIME($seconds) 返回被转化为小时、 分钟和秒数的seconds参数值, 其格式为 'HH:MM:SS' 或HHMMSS，具体格式根据该函数是否用在字符串或数字语境中而定
 * @method static sql\part STR_TO_DATE($str,$format) 这是DATE_FORMAT 函数的倒转。它获取一个字符串 str 和一个格式字符串format。若格式字符串包含日期和时间部分，则 STR_TO_DATE()返回一个 DATETIME 值， 若该字符串只包含日期部分或时间部分，则返回一个 DATE 或TIME值
 * @method static sql\part SUBDATE($expr,$days) 这是DATE_FORMAT 被第二个参数的 INTERVAL型式调用时, SUBDATE()和DATE_SUB()的意义相同。对于有关INTERVAL参数的信息， 见有关 DATE_ADD()的讨论
 * @method static sql\part SUBTIME($expr,$expr2) 从expr 中提取expr2 ，然后返回结果。expr 是一个时间或日期时间表达式，而xpr2 是一个时间表达式
 * @method static sql\part SYSDATE() 返回当前日期和时间值，格式为'YYYY-MM-DD HH:MM:SS' 或YYYYMMDDHHMMSS， 具体格式根据函数是否用在字符串或数字语境而定
 * @method static sql\part TIME($expr) 提取一个时间或日期时间表达式的时间部分，并将其以字符串形式返回
 * @method static sql\part TIMEDIFF($expr,$expr2) 返回起始时间 expr 和结束时间expr2 之间的时间。 expr 和expr2 为时间或 date-and-time 表达式,两个的类型必须一样
 * @method static sql\part TIMESTAMP($expr,$expr2=null) 对于一个单参数,该函数将日期或日期时间表达式 expr 作为日期时间值返回.对于两个参数, 它将时间表达式 expr2 添加到日期或日期时间表达式 expr 中，将theresult作为日期时间值返回
 * @method static sql\part TIMESTAMPADD($interval,$int_expr,$datetime_expr) 将整型表达式int_expr 添加到日期或日期时间表达式 datetime_expr中。 int_expr 的单位被时间间隔参数给定，该参数必须是以下值的其中一个： FRAC_SECOND、SECOND、 MINUTE、 HOUR、 DAY、 WEEK、 MONTH、 QUARTER或 YEAR
 * @method static sql\part TIMESTAMPDIFF($interval,$datetime_expr1,$datetime_expr2) 返回日期或日期时间表达式datetime_expr1 和datetime_expr2the 之间的整数差。其结果的单位由interval 参数给出。interval 的法定值同TIMESTAMPADD()函数说明中所列出的相同
 * @method static sql\part TIME_FORMAT($time,$format) 其使用和 DATE_FORMAT函数相同, 然而format 字符串可能仅会包含处理小时、分钟和秒的格式说明符。其它说明符产生一个NULL值或0
 * @method static sql\part TIME_TO_SEC($time) 返回已转化为秒的time参数
 * @method static sql\part TO_DAYS($date) 给定一个日期date, 返回一个天数 (从年份0开始的天数 )
 * @method static sql\part TO_SECONDS($expr) Given a date or datetime expr, returns the number of seconds since the year 0. If expr is not a valid date or datetime value, returns NULL.
 * @method static sql\part UNIX_TIMESTAMP($date=null) 若无参数调用，则返回一个Unix timestamp ('1970-01-01 00:00:00' GMT 之后的秒数) 作为无符号整数。若用date 来调用UNIX_TIMESTAMP()，它会将参数值以'1970-01-01 00:00:00' GMT后的秒数的形式返回。date 可以是一个DATE 字符串、一个 DATETIME字符串、一个 TIMESTAMP或一个当地时间的YYMMDD 或YYYMMDD格式的数字
 * @method static sql\part UTC_DATE() 返回当前 UTC日期值，其格式为 'YYYY-MM-DD' 或 YYYYMMDD，具体格式取决于函数是否用在字符串或数字语境中
 * @method static sql\part UTC_TIME() 返回当前 UTC 值，其格式为  'HH:MM:SS' 或HHMMSS，具体格式根据该函数是否用在字符串或数字语境而定
 * @method static sql\part UTC_TIMESTAMP() 返回当前UTC日期及时间值，格式为 'YYYY-MM-DD HH:MM:SS' 或YYYYMMDDHHMMSS，具体格式根据该函数是否用在字符串或数字语境而定
 * @method static sql\part WEEK($date,$mode=null) 该函数返回date 对应的星期数。WEEK() 的双参数形式允许你指定该星期是否起始于周日或周一， 以及返回值的范围是否为从0 到53 或从1 到53。若 mode参数被省略，则使用default_week_format系统自变量的值
 * @method static sql\part WEEKDAY($date) 返回date (0 = 周一, 1 = 周二, ... 6 = 周日)对应的工作日索引
 * @method static sql\part WEEKOFYEAR($date) 将该日期的阳历周以数字形式返回，范围是从1到53。它是一个兼容度函数，相当于WEEK(date,3)
 * @method static sql\part YEAR($date) 返回date 对应的年份,范围是从1000到9999
 * @method static sql\part YEARWEEK($date,$start) 返回一个日期对应的年或周。start参数的工作同 start参数对 WEEK()的工作相同。结果中的年份可以和该年的第一周和最后一周对应的日期参数有所不同
 *
 * 12.10 Cast Functions and Operators
 * BINARY		Cast a string to a binary string
 * CAST()		Cast a value as a certain type
 * CONVERT()	Cast a value as a certain type
 * @method static sql\part CAST($expr,$type)
 * @method static sql\part CONVERT($expr,$type) BINARY|CHAR|DATE|DATETIME|DECIMAL|JSON|NCHAR|SIGNED|TIME|UNSIGNED
 *
 * 12.11 XML Functions
 * ExtractValue()	Extract a value from an XML string using XPath notation
 * UpdateXML()		Return replaced XML fragment
 * @method static sql\part ExtractValue($xml_frag, $xpath_expr) ExtractValue('<a><b/></a>', '/a/b');
 * @method static sql\part UpdateXML($xml_target, $xpath_expr, $new_xml) UpdateXML('<a><b>ccc</b><d></d></a>', '/a', '<e>fff</e>')
 *
 * 12.12 Bit Functions and Operators
 * BIT_COUNT()	Return the number of bits that are set
 * &	Bitwise AND
 * ~	Bitwise inversion
 * |	Bitwise OR
 * ^	Bitwise XOR
 * <<	Left shift
 * >>	Right shift
 * @method static sql\part BIT_COUNT($N) 返回参数N 中所设置的比特数
 *
 * 12.13 Encryption and Compression Functions
 * @method static sql\part AES_ENCRYPT($str,$key_str) 这些函数允许使用官方AES进行加密和数据加密 (高级加密标准 ) 算法, 即以前人们所熟知的 “Rijndael”。 保密关键字的长度为128比特，不过你可以通过改变源而将其延长到 256 比特。我们选择了 128比特的原因是它的速度要快得多，且对于大多数用途而言这个保密程度已经够用
 * @method static sql\part AES_DECRYPT($crypt_str,$key_str) 这些函数允许使用官方AES进行加密和数据加密 (高级加密标准 ) 算法, 即以前人们所熟知的 “Rijndael”。 保密关键字的长度为128比特，不过你可以通过改变源而将其延长到 256 比特。我们选择了 128比特的原因是它的速度要快得多，且对于大多数用途而言这个保密程度已经够用
 * ASYMMETRIC_DECRYPT($N)
 * ASYMMETRIC_DERIVE($N)
 * ASYMMETRIC_ENCRYPT($N)
 * ASYMMETRIC_SIGN($N)
 * ASYMMETRIC_VERIFY($N)
 * @method static sql\part COMPRESS($string_to_compress) 压缩一个字符串。这个函数要求 MySQL已经用一个诸如zlib的压缩库压缩过。   否则，返回值始终是NULL。UNCOMPRESS() 可将压缩过的字符串进行解压缩
 * CREATE_ASYMMETRIC_PRIV_KEY($N)
 * CREATE_ASYMMETRIC_PUB_KEY($N)
 * CREATE_DH_PARAMETERS($N)
 * CREATE_DIGEST($N)
 * @method static sql\part DECODE($crypt_str,$key_str) 使用 pass_str 作为密码，解密加密字符串 crypt_str， crypt_str 应该是由ENCODE()返回的字符串
 * @method static sql\part DES_DECRYPT($crypt_str,$key_str=null) 使用DES_ENCRYPT()加密一个字符串。若出现错误，这个函数会返回 NULL
 * @method static sql\part DES_ENCRYPT($str,$key_str=null) 用Triple-DES 算法给出的关键字加密字符串。若出现错误，这个函数会返回NUL
 * @method static sql\part ENCODE($str,$key_str) 使用pass_str 作为密码，解密 str 。 使用DECODE()解密结果
 * @method static sql\part ENCRYPT($str,$salt=null) 使用Unix crypt() 系统调用加密 str。 salt 参数应为一个至少包含2个字符的字符串。若没有给出 salt 参数，则使用任意值
 * @method static sql\part MD5($str) 为字符串算出一个 MD5 128比特检查和。该值以32位十六进制数字的二进制字符串的形式返回, 若参数为 NULL 则会返回 NULL。例如，返回值可被用作散列关键字
 * @method static sql\part RANDOM_BYTES($len) This function returns a binary string of len random bytes generated using the random number generator of the SSL library. Permitted values of len range from 1 to 1024. For values outside that range, RANDOM_BYTES() generates a warning and returns NULL.
 * @method static sql\part SHA($str) synonymous with SHA1().
 * @method static sql\part SHA1($str) 为字符串算出一个 SHA1 160比特检查和，如RFC 3174 (安全散列算法 )中所述。该值被作为40位十六进制数字返回，而当参数为NULL 时则返回 NULL。这个函数的一个可能的用处就在于其作为散列关键字。你也可以将其作为存储密码的密码安全函数使用
 * @method static sql\part SHA2($str, $hash_length) Calculates the SHA-2 family of hash functions (SHA-224, SHA-256, SHA-384, and SHA-512). The first argument is the cleartext string to be hashed. The second argument indicates the desired bit length of the result, which must have a value of 224, 256, 384, 512, or 0 (which is equivalent to 256).
 * STATEMENT_DIGEST($statement) Given an SQL statement as a string, returns the statement digest hash value as a string in the connection character set, or NULL if the argument is NULL.
 * STATEMENT_DIGEST_TEXT($statement) Given an SQL statement as a string, returns the normalized statement digest as a string in the connection character set, or NULL if the argument is NULL.
 * @method static sql\part UNCOMPRESS($string_to_uncompress) 对经COMPRESS()函数压缩后的字符串进行解压缩。若参数为压缩值，则结果为 NULL。这个函数要求  MySQL 已被诸如zlib 之类的压缩库编译过。否则, 返回值将始终是 NULL
 * @method static sql\part UNCOMPRESSED_LENGTH($compressed_string) 返回压缩字符串压缩前的长度
 * @method static sql\part VALIDATE_PASSWORD_STRENGTH($str) Given an argument representing a cleartext password, this function returns an integer to indicate how strong the password is. The return value ranges from 0 (weak) to 100 (strong).
 *
 * 12.14 Locking Functions
 * GET_LOCK()	Get a named lock
 * IS_FREE_LOCK()	Whether the named lock is free
 * IS_USED_LOCK()	Whether the named lock is in use; return connection identifier if true
 * RELEASE_ALL_LOCKS()	Release all current named locks
 * RELEASE_LOCK()	Release the named lock
 *
 * 12.15 Information Functions
 * @method static sql\part BENCHMARK($count,$expr) 函数重复count 次执行表达式 expr 。 它可以被用于计算  MySQL 处理表达式的速度。结果值通常为 0。另一种用处来自 mysql客户端内部,能够报告问询执行的次数
 * @method static sql\part CHARSET($str) Returns the character set of the string argument.
 * @method static sql\part COERCIBILITY($str) Returns the collation coercibility value of the string argument.
 * @method static sql\part COLLATION($str) 返回惠字符串参数的排序方式
 * @method static sql\part CONNECTION_ID() 返回对于连接的连接ID 线程ID。每个连接都有各自的唯一 ID
 * @method static sql\part CURRENT_ROLE() Returns a utf8 string containing the current active roles for the current session, separated by commas, or NONE if there are none. The value reflects the setting of the sql_quote_show_create system variable.
 * @method static sql\part CURRENT_USER() 返回当前话路被验证的用户名和主机名组合。这个值符合确定你的存取权限的MySQL 账户。在被指定SQL SECURITY DEFINER特征的存储程序内， CURRENT_USER() 返回程序的创建者
 * @method static sql\part DATABASE() 返回使用 utf8 字符集的默认(当前)数据库名。在存储程序里，默认数据库是同该程序向关联的数据库，但并不一定与调用语境的默认数据库相同
 * @method static sql\part FOUND_ROWS() A SELECT语句可能包括一个 LIMIT 子句，用来限制服务器返回客户端的行数。在有些情况下，需要不用再次运行该语句而得知在没有LIMIT 时到底该语句返回了多少行。为了知道这个行数, 包括在SELECT 语句中选择  SQL_CALC_FOUND_ROWS ，随后调用 FOUND_ROWS()
 * @method static sql\part ICU_VERSION() The version of the International Components for Unicode (ICU) library used to support regular expression operations (see Section 12.5.2, “Regular Expressions”). This function is primarily intended for use in test cases.
 * @method static sql\part LAST_INSERT_ID($expr=null) 自动返回最后一个INSERT或 UPDATE 问询为 AUTO_INCREMENT列设置的第一个 发生的值
 * @method static sql\part ROLES_GRAPHML() Returns a utf8 string containing a GraphML document representing memory role subgraphs.
 * @method static sql\part ROW_COUNT() ROW_COUNT返回被前面语句升级的、插入的或删除的行数。 这个行数和 mysql 客户端显示的行数及 mysql_affected_rows() C API 函数返回的值相同
 * @method static sql\part SCHEMA() synonym for DATABASE().
 * @method static sql\part SESSION_USER() synonym for USER().
 * @method static sql\part SYSTEM_USER() synonym for USER().
 * @method static sql\part USER() 返回当前 MySQL用户名和机主名
 * @method static sql\part VERSION() 返回指示 MySQL 服务器版本的字符串。这个字符串使用 utf8 字符集
 *
 * 12.16 Spatial Analysis Functions
 * 12.17 JSON Functions
 * JSON_ARRAY(...$values) JSON_ARRAY(1, "abc", NULL, TRUE, CURTIME()) [1, "abc", null, true, "11:30:24.000000"] Evaluates a (possibly empty) list of values and returns a JSON array containing those values.
 * JSON_OBJECT(...$keyThenValue) JSON_OBJECT('id', 87, 'name', 'carrot') {"id": 87, "name": "carrot"} Evaluates a (possibly empty) list of key-value pairs and returns a JSON object containing those pairs. An error occurs if any key name is NULL or the number of arguments is odd.
 * JSON_QUOTE($string) JSON_QUOTE('null') "null" Quotes a string as a JSON value by wrapping it with double quote characters and escaping interior quote and other characters, then returning the result as a utf8mb4 string. Returns NULL if the argument is NULL.
 * JSON_CONTAINS($target, $candidate, $path=null) Indicates by returning 1 or 0 whether a given candidate JSON document is contained within a target JSON document, or—if a path argument was supplied—whether the candidate is found at a specific path within the target. Returns NULL if any argument is NULL, or if the path argument does not identify a section of the target document.
 * JSON_CONTAINS_PATH($json_doc, $oneORAll, ...$paths) Returns 0 or 1 to indicate whether a JSON document contains data at a given path or paths. Returns NULL if any argument is NULL. An error occurs if the json_doc argument is not a valid JSON document, any path argument is not a valid path expression, or one_or_all is not 'one' or 'all'.
 * JSON_EXTRACT($json_doc, ...$paths) JSON_EXTRACT('[10, 20, [30, 40]]', '$[1]', '$[0]') [20, 10] "$.id" Returns data from a JSON document, selected from the parts of the document matched by the path arguments. Returns NULL if any argument is NULL or no paths locate a value in the document.
 * JSON_KEYS($json_doc, $path) JSON_KEYS('{"a": 1, "b": {"c": 30}}') ["a", "b"] Returns the keys from the top-level value of a JSON object as a JSON array, or, if a path argument is given, the top-level keys from the selected path. Returns NULL if any argument is NULL, the json_doc argument is not an object, or path, if given, does not locate an object.
 * JSON_SEARCH($json_doc, $oneORAll, $search_str, $escape_char, ...$paths) Returns the path to the given string within a JSON document. Returns NULL if any of the json_doc, search_str, or path arguments are NULL; no path exists within the document; or search_str is not found.
 * JSON_ARRAY_APPEND($json_doc, ...$pathThenValue) Appends values to the end of the indicated arrays within a JSON document and returns the result. Returns NULL if any argument is NULL.
 * JSON_ARRAY_INSERT($json_doc, ...$pathThenValue) Updates a JSON document, inserting into an array within the document and returning the modified document. Returns NULL if any argument is NULL.
 * JSON_INSERT($json_doc, ...$pathThenValue) Inserts data into a JSON document and returns the result. Returns NULL if any argument is NULL.
 * JSON_MERGE_PATCH(...$json_docs) Performs an RFC 7396 compliant merge of two or more JSON documents and returns the merged result, without preserving members having duplicate keys.
 * JSON_MERGE_PRESERVE(...$json_docs) Merges two or more JSON documents and returns the merged result. Returns NULL if any argument is NULL.
 * JSON_REMOVE($json_doc, ...$paths) Removes data from a JSON document and returns the result. Returns NULL if any argument is NULL.
 * JSON_REPLACE($json_doc, ...$pathThenValue) Replaces existing values in a JSON document and returns the result. Returns NULL if any argument is NULL.
 * JSON_SET($json_doc, ...$pathThenValue) Inserts or updates data in a JSON document and returns the result. Returns NULL if any argument is NULL or path, if given, does not locate an object.
 * JSON_UNQUOTE($json_val) Unquotes JSON value and returns the result as a utf8mb4 string. Returns NULL if the argument is NULL.
 * JSON_DEPTH($json_doc) Returns the maximum depth of a JSON document. Returns NULL if the argument is NULL.
 * JSON_LENGTH($json_doc) Returns the length of a JSON document, or, if a path argument is given, the length of the value within the document identified by the path. Returns NULL if any argument is NULL or the path argument does not identify a value in the document.
 * JSON_TYPE($json_val) Returns a utf8mb4 string indicating the type of a JSON value.
 * JSON_VALID($val) Returns 0 or 1 to indicate whether a value is valid JSON. Returns NULL if the argument is NULL.
 * 12.17.6 JSON Table Functions
 * JSON_TABLE(expr, path COLUMNS (column_list) [AS] alias)
 * JSON_TABLE(
 *          expr,
 *          path COLUMNS (column_list)
 * )   [AS] alias
 * column_list:
 *          column[, column][, ...]
 * column:
 *          name FOR ORDINALITY
 *          |  name type PATH string path [on_error] [on_empty]
 *          |  name type EXISTS PATH string path
 *          |  NESTED [PATH] path COLUMNS (column_list)
 * on_error:
 *          {NULL | ERROR | DEFAULT json_string} ON ERROR
 * on_empty:
 *          {NULL | ERROR | DEFAULT json_string} ON EMPTY
 * JSON_PRETTY($json_val) Provides pretty-printing of JSON values similar to that implemented in PHP and by other languages and database systems.
 * JSON_STORAGE_FREE($json_val) For a JSON column value, this function shows how much storage space was freed in its binary representation after it was updated in place using JSON_SET(), JSON_REPLACE(), or JSON_REMOVE().
 * JSON_STORAGE_SIZE($json_val) This function returns the number of bytes used to store the binary representation of a JSON document.
 *
 * 12.18 Functions Used with Global Transaction Identifiers (GTIDs)
 * 12.19 MySQL Enterprise Encryption Functions
 * 12.20 Aggregate (GROUP BY) Functions
 * @method static sql\part AVG($expr,$DISTINCT=false) 返回expr 的平均值。 DISTINCT 选项可用于返回 expr的不同值的平均值
 * @method static sql\part BIT_AND($expr) 返回expr中所有比特的 bitwise AND 。计算执行的精确度为64比特(BIGINT) 若找不到匹配的行，则这个函数返回 18446744073709551615 。(这是无符号 BIGINT 值，所有比特被设置为 1）
 * @method static sql\part BIT_OR($expr) 返回expr 中所有比特的bitwise OR。计算执行的精确度为64比特(BIGINT) 若找不到匹配的行，则函数返回 0
 * @method static sql\part BIT_XOR($expr) 返回expr 中所有比特的bitwise XOR。计算执行的精确度为64比特(BIGINT) 。若找不到匹配的行，则函数返回 0
 * @method static sql\part COUNT($expr,$DISTINCT=false) 返回SELECT语句检索到的行中非NULL值的数目。若找不到匹配的行，则COUNT() 返回 0
 * @method static sql\part GROUP_CONCAT($expr) 该函数返回带有来自一个组的连接的非NULL值的字符串结果
 * JSON_ARRAYAGG()
 * JSON_OBJECTAGG()
 * @method static sql\part MIN($expr,$DISTINCT=false) 返回expr 的最小值和最大值
 * @method static sql\part MAX($expr,$DISTINCT=false) 返回expr 的最小值和最大值
 * @method static sql\part STD($expr) 返回expr 的总体标准偏差。这是标准 SQL 的延伸。这个函数的STDDEV() 形式用来提供和Oracle 的兼容性。可使用标准SQL函数 STDDEV_POP() 进行代替
 * @method static sql\part STDDEV($expr) 返回expr 的总体标准偏差。这是标准 SQL 的延伸。这个函数的STDDEV() 形式用来提供和Oracle 的兼容性。可使用标准SQL函数 STDDEV_POP() 进行代替
 * @method static sql\part STDDEV_POP($expr) 返回expr 的总体标准偏差(VAR_POP()的平方根)。你也可以使用  STD() 或STDDEV(), 它们具有相同的意义，然而不是标准的 SQL
 * @method static sql\part STDDEV_SAMP($expr) 返回expr 的样本标准差 ( VAR_SAMP()的平方根)
 * @method static sql\part SUM($expr,$DISTINCT=false) 返回expr 的总数。 若返回集合中无任何行，则 SUM() 返回NULL。DISTINCT 关键词可用于 MySQL 5.1 中，求得expr 不同值的总和
 * @method static sql\part VAR_POP($expr) 返回expr 总体标准方差。它将行视为总体，而不是一个样本， 所以它将行数作为分母。你也可以使用 VARIANCE(),它具有相同的意义然而不是 标准的 SQL
 * @method static sql\part VAR_SAMP($expr) 返回expr 的样本方差。更确切的说，分母的数字是行数减去1
 * @method static sql\part VARIANCE($expr) 返回expr 的总体标准方差。这是标准SQL 的延伸。可使用标准SQL 函数 VAR_POP() 进行代替
 * 12.21 Window Functions
 * 12.22 Performance Schema Functions
 * 12.23 Internal Functions
 * 12.24 Miscellaneous Functions
 * @method static sql\part ANY_VALUE($arg) This function is useful for GROUP BY queries when the ONLY_FULL_GROUP_BY SQL mode is enabled, for cases when MySQL rejects a query that you know is valid for reasons that MySQL cannot determine. The function return value and type are the same as the return value and type of its argument, but the function result is not checked for the ONLY_FULL_GROUP_BY SQL mode.
 * @method static sql\part BIN_TO_UUID($binary_uuid, $swap_flag) It converts a binary UUID to a string UUID and returns the result. The binary value should be a UUID as a VARBINARY(16) value. The return value is a utf8 string of five hexadecimal numbers separated by dashes.
 * @method static sql\part DEFAULT($col_name) 返回一个表列的默认值。若该列没有默认值则会产生错误
 * FORMAT($X,$D) 将数字X 的格式写为'#,###,###.##',以四舍五入的方式保留小数点后 D 位， 并将结果以字符串的形式返回。若  D 为 0, 则返回结果不带有小数点，或不含小数部分
 * GROUPING(expr [, expr] ...)
 * @method static sql\part INET_ATON($expr) 给出一个作为字符串的网络地址的点地址表示，返回一个代表该地址数值的整数。地址可以是4或8比特地址
 * @method static sql\part INET_NTOA($expr) 给定一个数字网络地址 (4 或 8 比特),返回作为字符串的该地址的电地址表示
 * @method static sql\part INET6_ATON($expr) Given an IPv6 or IPv4 network address as a string, returns a binary string that represents the numeric value of the address in network byte order (big endian).
 * @method static sql\part INET6_NTOA($expr) Given an IPv6 or IPv4 network address represented in numeric form as a binary string, returns the string representation of the address as a string in the connection character set. If the argument is not a valid address, INET6_NTOA() returns NULL.
 * @method static sql\part IS_IPV4($expr) Returns 1 if the argument is a valid IPv4 address specified as a string, 0 otherwise.
 * @method static sql\part IS_IPV4_COMPAT($expr) This function takes an IPv6 address represented in numeric form as a binary string, as returned by INET6_ATON(). It returns 1 if the argument is a valid IPv4-compatible IPv6 address, 0 otherwise. IPv4-compatible addresses have the form ::ipv4_address.
 * @method static sql\part IS_IPV4_MAPPED($expr) This function takes an IPv6 address represented in numeric form as a binary string, as returned by INET6_ATON(). It returns 1 if the argument is a valid IPv4-mapped IPv6 address, 0 otherwise. IPv4-mapped addresses have the form ::ffff:ipv4_address.
 * @method static sql\part IS_IPV6($expr) Returns 1 if the argument is a valid IPv6 address specified as a string, 0 otherwise. This function does not consider IPv4 addresses to be valid IPv6 addresses.
 * @method static sql\part IS_UUID($string_uuid) Returns 1 if the argument is a valid string-format UUID, 0 if the argument is not a valid UUID, and NULL if the argument is NULL.
 * @method static sql\part MASTER_POS_WAIT($log_name,$log_pos,$timeout=null) 该函数对于控制主从同步很有用处。它会持续封锁，直到从设备阅读和应用主机记录中所有补充资料到指定的位置。返回值是其为到达指定位置而必须等待的记录事件的数目。若从设备SQL线程没有被启动、从设备主机信息尚未初始化、参数不正确或出现任何错误，则该函数返回 NULL。若超时时间被超过，则返回-1。若在MASTER_POS_WAIT() 等待期间，从设备SQL线程中止，则该函数返回 NULL。若从设备由指定位置通过，则函数会立即返回结果
 * @method static sql\part NAME_CONST($name,$value) 返回给定值。 当用来产生一个结果集合列时, NAME_CONST()促使该列使用给定名称
 * @method static sql\part RELEASE_LOCK($str) 解开被GET_LOCK()获取的，用字符串str 所命名的锁。若锁被解开，则返回  1，若改线程尚未创建锁，则返回0 (此时锁没有被解开 ), 若命名的锁不存在，则返回 NULL。若该锁从未被对GET_LOCK()的调用获取，或锁已经被提前解开，则该锁不存在
 * @method static sql\part SLEEP($duration) 睡眠(暂停) 时间为duration 参数给定的秒数，然后返回 0。若 SLEEP() 被中断,它会返回 1。 duration 或许或包括一个给定的以微秒为单位的分数部分
 * @method static sql\part UUID() 返回一个通用唯一标识符 UUID ，其产生的根据是《DCE 1.1: 远程过程调用》 (附录A) CAE (公共应用软件环境) 的说明，该作品于1997年10月由 The Open Group 出版 (文件编号 C706, http://www.opengroup.org/public/pubs/catalog/c706.htm).
 * @method static sql\part UUID_SHORT() Returns a “short” universal identifier as a 64-bit unsigned integer.
 * @method static sql\part UUID_TO_BIN($string_uuid, $swap_flag) Converts a string UUID to a binary UUID and returns the result.
 * @method static sql\part VALUES($col_name) 在一个INSERT … ON DUPLICATE KEY UPDATE …语句中，你可以在UPDATE 子句中使用 VALUES(col_name)函数，用来访问来自该语句的INSERT 部分的列值。换言之，UPDATE 子句中的 VALUES(col_name) 访问需要被插入的col_name 的值,并不会发生重复键冲突。这个函数在多行插入中特别有用。  VALUES()函数只在INSERT ... UPDATE 语句中有意义，而在其它情况下只会返回 NULL
 */
class sql implements \ArrayAccess{
	/**
	 * 选项指定是否重复行应被返回。如果这些选项没有被给定，则默认值为ALL（所有的匹配行被返回）。DISTINCT和DISTINCTROW是同义词，用于指定结果集合中的重复行应被删除。
	 */
	const OPTS_DISTINCT ='DISTINCT';
	/**
	 * 给予SELECT更高的优先权，高于用于更新表的语句
	 */
	const OPTS_HIGH_PRIORITY ='HIGH_PRIORITY';
	/**
	 * 用于促使优化符把表联合在一起，顺序按照这些表在FROM子句中排列的顺序
	 */
	const OPTS_STRAIGHT_JOIN ='STRAIGHT_JOIN';
	/**
	 * 可以与GROUP BY或DISTINCT同时使用，来告知优化符结果集合是较小的。在此情况下，MySAL使用快速临时表来储存生成的表，而不是使用分类。
	 */
	const OPTS_SQL_SMALL_RESULT ='SQL_SMALL_RESULT';
	/**
	 * 可以与GROUP BY或DISTINCT同时使用，来告知优化符结果集合有很多行。在这种情况下，MySQL直接使用以磁盘为基础的临时表（如果需要的话）。
	 */
	const OPTS_SQL_BIG_RESULT ='SQL_BIG_RESULT';
	/**
	 * 促使结果被放入一个临时表中。这可以帮助MySQL提前解开表锁定，在需要花费较长时间的情况下，也可以帮助把结果集合发送到客户端中。
	 */
	const OPTS_SQL_BUFFER_RESULT ='SQL_BUFFER_RESULT';
	/**
	 * 告知MySQL不要把查询结果存储在查询缓存中。
	 */
	const OPTS_SQL_NO_CACHE ='SQL_NO_CACHE';
	/**
	 * 告知MySQL计算有多少行应位于结果集合中，不考虑任何LIMIT子句。行的数目可以使用SELECT FOUND_ROWS()恢复
	 */
	const OPTS_SQL_CALC_FOUND_ROWS ='SQL_CALC_FOUND_ROWS';

	const JOIN_INNER ='INNER';
	const JOIN_CROSS ='CROSS';
	const JOIN_STRAIGHT ='STRAIGHT';
	const JOIN_LEFT ='LEFT';
	const JOIN_RIGHT ='RIGHT';
	const JOIN_NATURAL ='NATURAL';

	//protected $table =[];
	protected $where =[];
	protected $select =null;
	protected $limit =null;
	protected $sort =null;
	protected $join =[];
	protected $group =null;
	protected $having =[];
	/**
	 * 操作
	 * @var string
	 */
	protected $action ='';
	/**
	 * 操作配置参数
	 * @var array
	 */
	protected $options =[];
	/**
	 * 创建或更新内容
	 * @var array
	 */
	protected $set =[];//
	/**
	 * 表名 单表逻辑
	 * @var string
	 */
	protected $table ='';
	/**
	 * 表名 别名
	 * @var string
	 */
	protected $tableAS ='';
	/**
	 * 当前表的主键名
	 * @var string
	 */
	protected $primary ='id';
	/**
	 * @var \nx\helpers\db\pdo
	 */
	protected $db =null;
	/**
	 * 执行参数
	 * @var array
	 */
	public $params =[];
	public $collectParams =true;
	/**
	 * 是否只返回第一条数据
	 * @var bool
	 */
	private $first =false;
	/**
	 * sql constructor.
	 * @param string                  $tableName
	 * @param string                  $primary
	 * @param \nx\helpers\db\pdo|null $db
	 */
	public function __construct(string $tableName, string $primary='id', pdo $db=null){
		$n =explode(' ', $tableName, 2);
		$this->table =$n[0];
		$this->tableAS =$n[1] ?? '';
		$this->primary =$primary;
		$this->db =$db;
	}
	/**
	 * 执行sql语句
	 * @param \nx\helpers\db\pdo|null $db
	 * @return \nx\helpers\db\pdo\result
	 */
	public function execute(pdo $db=null){
		$pdo =$db ?? $this->db ?? null;
		$sql =(string)$this;
		switch($this->action){
			case 'insert':
				return $pdo->insert($sql, $this->params);
			case 'update':
			case 'delete':
				return $pdo->execute($sql, $this->params);
			case 'select':
				return $pdo->select($sql, $this->params);
		}
	}
	//-------------------------------------------------------------------------------------------------------------
	/**
	 * 向表格中插入数据 insert
	 * @param array[string $field =>any] $fields
	 * @param array $options
	 * @return \nx\helpers\db\sql
	 */
	public function create($fields=[], array $options=[]):sql{
		$this->set =$fields;
		$this->options =$options;
		$this->action ='insert';
		return $this;
	}
	public function update($fields=[], array $options=[]):sql{
		$this->set =$fields;
		$this->options =$options;
		$this->action ='update';
		return $this;
	}
	public function delete(array $options=[]):sql{
		$this->options =$options;
		$this->action ='delete';
		return $this;
	}
	public function select($fields=[], array $options=[]):sql{
		$this->select=$fields;
		$this->options =$options;
		$this->action ='select';
		return $this;
	}
	//-------------------------------------------------------------------------------------------------------------
	/**
	 * https://dev.mysql.com/doc/refman/8.0/en/join.html
	 * @param \nx\helpers\db\sql $table2
	 * @param string|null        $on  USING => ['id'], ON => ['id'=>'id'], ['id'=>$user['id']] $user['id'] $user('123')
	 * @param array              $options
	 * @return \nx\helpers\db\sql
	 */
	public function join(sql $table2, $on=null, array $options=[]):sql{
		$this->join[] =[$table2, $on, $options];
		return $this;
	}
	//-------------------------------------------------------------------------------------------------------------
	public function where(...$conditions):sql{
		$this->where=$conditions;
		return $this;
	}
	public function limit(int $rows, int $offset=0):sql{
		$this->first =($rows===1);
		$this->limit =[$rows, $offset];
		return $this;
	}
	public function page(int $page, int $max=20):sql{
		$this->first =false;
		$this->limit =[$max, ($page -1)*$max];
		return $this;
	}
	public function sort($fields=null, $sort='ASC'):sql{
		$this->sort =[$fields, $sort];
		return $this;
	}
	public function group($fields=[], $sort='ASC'):sql{
		$this->group =[$fields, $sort];
		return $this;
	}
	public function having(...$conditions):sql{
		$this->having =$conditions;
		return $this;
	}
	//-------------------------------------------------------------------------------------------------------------
	/**
	 * 设置别名
	 * @param string $name
	 * @return \nx\helpers\db\sql
	 */
	public function as(string $name):sql{
		$tab =clone $this;
		$tab->tableAS =$name;
		return $tab;
	}
	public function formatField($name=null, $withTable =true):string{
		if($name instanceof sql\part) return (string)$name;
		$value =$name??$this->primary;
		$field = ('*'===$value) ?$value :"`{$value}`";
		$table =$this->tableAS ?"`{$this->tableAS}`" :"`{$this->table}`";
		return $withTable ?"{$table}.{$field}" :$field;
	}
	public function getFormatName($withAS =true):string{
		if($withAS) return $this->tableAS ?"`{$this->table}` `{$this->tableAS}`" : "`{$this->table}`";
		else return "`{$this->table}`";
	}
	public static function formatValue($value, sql $table=null){
		switch(gettype($value)){
			case 'object':
				if($table && $table->collectParams && ($value instanceof sql\part)){
					if('value' ===$value->type){
						$table->params[] =$value;
						return '?';
					}
				}
				return (string)$value;
			case 'string':
				if('*'===$value) return "*";
				if('\*'===$value) $value ='*';
				if($table && $table->collectParams){
					$table->params[]=$value;//(string)
					return '?';
				}
				return "\"{$value}\"";
			case 'boolean':
				return $value ?'TRUE' :'FALSE';
			case 'NULL':
				return 'NULL';
			case 'array':
				if($table && $table->collectParams){
					$table->params =array_merge($table->params, $value);
					return implode(',', array_fill(0, count($value), '?'));
				} else return (string)$value;
			default:
				if($table && $table->collectParams){
					$table->params[]=$value;//(string)
					return '?';
				} else return (string)$value;
			//default:
			//	return (string)$value;
		}
	}
	//-------------------------------------------------------------------------------------------------------------
	protected function buildWhere($where, $command ='WHERE'):string{
		if(count($where) >0){
			$_conditions=[];
			foreach($where as $cond){
				if(is_array($cond)){// ->where(['id'=>1, 'status'=>2, sql\part()])
					foreach($cond as $field=>$value){// id => 1 , any =>sql\part()
						$_conditions[] =$value instanceof sql\part? $value : $this[$field]->equal($this($value));
					}
					continue;
				} elseif(!($cond instanceof sql\part)) $cond =$this[null]->equal($this($cond));
				$_conditions[] =$cond;//todo 值收集关联到当前表(联合查询时其中一表条件值的收集)
			}
			$_where =count($_conditions) ? " {$command} ".implode(' AND ', $_conditions) : '';
		} else $_where ='';
		return $_where;
	}
	public function buildSelect($only =true):string{
		$select =$this->select;
		if(null ===$select) return '';
		elseif(!is_array($select)) $select=[$select];
		if(count($select) >0){
			$_fields =[];
			foreach($select as $field){
				$_fields[] =$field instanceof sql\part ?$field :new sql\part($field, 'field', $this);
			}
			$_select =implode(', ', $_fields);
		} else $_select =$only ?'*' :$this->formatField("*");
		return $_select;
	}
	protected function buildInsertValue($set):array{
		if(!is_array($set) || empty($set)) return [[],[],null];
		$cols =current($set);
		if(!is_array($cols)){
			$cols =$set;
			$_set =[$set];
		} else $_set =$set;

		$is_named =false;
		$_cols =[];
		$_prepares =[];
		foreach($cols as $col=>$value){
			$_cols[] ="`{$col}`";//$col instanceof sql\part ?$col :new sql\part($col, 'field', $table);
			$_prepares[] =$is_named ?':'.$col->value :'?';
		}
		$params =[];
		foreach($_set as $index=>$values){
			if(!$is_named){
				$params[$index] =array_values($values);
			} else {
				$kv =[];
				foreach($values as $_col=>$value){
					$kv[':'.$_col] =$value;
				}
				$params[$index] =$kv;
			}
		}
		return [implode(', ', $_cols), implode(', ', $_prepares), $params];
	}
	protected function buildSet($set):string{
		if(!is_array($set) || empty($set)) return '';

		$params =[];
		foreach($set as $field=>$value){//default -> value

			$_value =$this::formatValue($value, $this);
			$_field =$this->formatField($field);
			$params[] ="{$_field} = {$_value}";
		}
		return " SET ".implode(', ', $params);
	}
	private function buildSortSC($_asc):string{
		$_sort ='ASC';
		if(is_bool($_asc)) $_sort =($_asc) ?'ASC' :'DESC';
		elseif(is_string($_asc)){
			$_sort =(strtolower($_asc[0]) === 'a') ?'ASC' :'DESC';
		}
		return $_sort;
	}
	protected function buildSort($sort, $command="ORDER"):string{
		if(empty($sort)) return '';
		[$field, $asc]=$sort ?? [null, true];//(field, asc)

		$_sorts =[];
		if(null ===$field) return '';
		elseif(is_array($field)){//(['a'=>'asc', 'b'=>'desc, $part=>'desc', 'd'], 'asc')
			foreach($field as $_field=>$_sort){
				$_f =$_field;
				$_s =$_sort;
				if(is_string($_field)) {
					$_f =$this->formatField($_field);
					$_s =$this->buildSortSC($_sort ?? $sort);
				} elseif(is_numeric($_field)){
					$_f =$this->formatField($_sort);
					$_s =$this->buildSortSC($sort);
				} elseif($_field instanceof sql\part) {
					$_f =$field->getAs() ?"`{$field->getAs()}`": (string)$field;
					$_s =$this->buildSortSC($_sort ?? $sort);
				} else trigger_error('无效的字段类型');
				$_sorts[$_f] =$_s;
			}
		}
		elseif($field instanceof sql\part) $_sorts[$field->getAs() ?"`{$field->getAs()}`": (string)$field] =$this->buildSortSC($asc);
		elseif(is_string($field)) $_sorts[$this->formatField($field)] =$this->buildSortSC($asc);
		else trigger_error('无效的字段类型');
		$_s =[];
		foreach($_sorts as $_field =>$_asc){
			$_s[] ='GROUP'===$command ? $_field : "{$_field} {$_asc}";
		}
		return count($_s) ?" {$command} BY ".implode(", ", $_s) :'';
	}
	protected function buildLimit($limit):string{
		if(empty($limit)) return '';
		list($rows, $offset) =$limit ?? ['', 0];
		if(empty($rows)) return '';
		return 0 === $offset ?" LIMIT {$rows}" :" LIMIT {$offset}, {$rows}";
	}
	public function __toString(){
		$this->params=[];//清空所有参数，重新构建
		switch($this->action){
			case 'delete':
				/**
				 * 13.2.2 DELETE Syntax
				 * DELETE [LOW_PRIORITY] [QUICK] [IGNORE] FROM tbl_name [[AS] tbl_alias]
				 * 				[PARTITION (partition_name [, partition_name] ...)]
				 * 				[WHERE where_condition]
				 * 				[ORDER BY ...]
				 * 				[LIMIT row_count]
				 */
				$priority =($this->options['priority'] ??false) ?' LOW_PRIORITY' :''; //LOW_PRIORITY
				$ignore =($this->options['ignore'] ??false) ?' IGNORE' :''; //IGNORE
				$quick =($this->options['quick'] ??false) ?' QUICK' :''; //QUICK
				$table =$this->getFormatName();
				$where =$this->buildWhere($this->where);
				$order =$this->buildSort($this->sort);
				$limit =$this->buildLimit($this->limit);
				return "DELETE{$priority}{$quick}{$ignore} FROM {$table}{$where}{$order}{$limit}";
			case 'insert':
				/**
				 * 13.2.6 INSERT Syntax
				 * INSERT [LOW_PRIORITY | DELAYED | HIGH_PRIORITY] [IGNORE]
				 * 				[INTO] tbl_name
				 * 				[PARTITION (partition_name [, partition_name] ...)]
				 * 				[(col_name [, col_name] ...)]
				 * 				{VALUES | VALUE} (value_list) [, (value_list)] ...
				 * 				[ON DUPLICATE KEY UPDATE assignment_list] //todo change to this mode
				 * INSERT [LOW_PRIORITY | DELAYED | HIGH_PRIORITY] [IGNORE]
				 * 				[INTO] tbl_name
				 * 				[PARTITION (partition_name [, partition_name] ...)]
				 * 				SET assignment_list
				 * 				[ON DUPLICATE KEY UPDATE assignment_list]
				 * INSERT [LOW_PRIORITY | HIGH_PRIORITY] [IGNORE] //todo support
				 * 				[INTO] tbl_name
				 * 				[PARTITION (partition_name [, partition_name] ...)]
				 * 				[(col_name [, col_name] ...)]
				 * 				SELECT ...
				 * 				[ON DUPLICATE KEY UPDATE assignment_list]
				 * value:
				 * 				{expr | DEFAULT}
				 * value_list:
				 * 				value [, value] ...
				 * assignment:
				 * 				col_name = value
				 * assignment_list:
				 * 				assignment [, assignment] ...
				 */
				$priority =($this->options['priority'] ??false) ?' '.strtoupper($this->options['priority']) :''; //LOW_PRIORITY | DELAYED | HIGH_PRIORITY
				$ignore =($this->options['ignore'] ??false) ?' IGNORE' :''; //IGNORE
				//$set =[]; //ON DUPLICATE KEY UPDATE col_name=expr, ...
				list($cols, $prepares, $this->params) =$this->buildInsertValue($this->set);
				$table =$this->getFormatName(false);
				return "INSERT{$priority}{$ignore} INTO {$table} ({$cols}) VALUES ($prepares)";
			case 'update':
				/**
				 * 13.2.12 UPDATE Syntax
				 * UPDATE [LOW_PRIORITY] [IGNORE] table_reference
				 * 				SET assignment_list
				 * 				[WHERE where_condition]
				 * 				[ORDER BY ...]
				 * 				[LIMIT row_count]
				 * value:
				 * 				{expr | DEFAULT}
				 * assignment:
				 * 				col_name = value
				 * assignment_list:
				 * 				assignment [, assignment] ...
				 */
				$priority =($this->options['priority'] ??false) ?' '.strtoupper($this->options['priority']) :''; //LOW_PRIORITY
				$ignore =($this->options['ignore'] ??false) ?' IGNORE' :''; //IGNORE
				$set =$this->buildSet($this->set);
				$where =$this->buildWhere($this->where);
				//$multiple =count($this->table)>1;
				//if($multiple){
				//	return "UPDATE{$priority}{$ignore} {$table} {$set} {$where}";
				//}
				$order =$this->buildSort($this->sort);
				$limit =$this->buildLimit($this->limit);
				$table =$this->getFormatName();
				return "UPDATE{$priority}{$ignore} {$table} {$set}{$where}{$order}{$limit}";
			case 'select':
				/**
				 * 13.2.10 SELECT Syntax
				 * SELECT
				 * 		[ALL | DISTINCT | DISTINCTROW ]	//选项指定是否重复行应被返回。如果这些选项没有被给定，则默认值为ALL（所有的匹配行被返回）。DISTINCT和DISTINCTROW是同义词，用于指定结果集合中的重复行应被删除。
				 * 			[HIGH_PRIORITY]			//给予SELECT更高的优先权，高于用于更新表的语句
				 * 			[STRAIGHT_JOIN]			//用于促使优化符把表联合在一起，顺序按照这些表在FROM子句中排列的顺序
				 * 			[SQL_SMALL_RESULT]		//可以与GROUP BY或DISTINCT同时使用，来告知优化符结果集合是较小的。在此情况下，MySAL使用快速临时表来储存生成的表，而不是使用分类。
				 * 			[SQL_BIG_RESULT]		//可以与GROUP BY或DISTINCT同时使用，来告知优化符结果集合有很多行。在这种情况下，MySQL直接使用以磁盘为基础的临时表（如果需要的话）。
				 * 			[SQL_BUFFER_RESULT]		//促使结果被放入一个临时表中。这可以帮助MySQL提前解开表锁定，在需要花费较长时间的情况下，也可以帮助把结果集合发送到客户端中。
				 * 			[SQL_NO_CACHE]			//告知MySQL不要把查询结果存储在查询缓存中。
				 * 			[SQL_CALC_FOUND_ROWS]	//告知MySQL计算有多少行应位于结果集合中，不考虑任何LIMIT子句。行的数目可以使用SELECT FOUND_ROWS()恢复
				 * 		select_expr [, select_expr ...]
				 * 		[FROM table_references
				 * 			[PARTITION partition_list]		//?
				 * 			[WHERE where_condition]
				 * 			[GROUP BY {col_name | expr | position}, ... [WITH ROLLUP]]
				 * 			[HAVING where_condition]
				 * 			[WINDOW window_name AS (window_spec)	//?
				 * 				[, window_name AS (window_spec)] ...]
				 * 			[ORDER BY {col_name | expr | position}
				 * 				[ASC | DESC], ... [WITH ROLLUP]]
				 * 			[LIMIT {[offset,] row_count | row_count OFFSET offset}]
				 * 			[INTO OUTFILE 'file_name'
				 * 				[CHARACTER SET charset_name]
				 * 				export_options
				 * 			| INTO DUMPFILE 'file_name'
				 * 			| INTO var_name [, var_name]]
				 * 			[FOR {UPDATE | SHARE} [OF tbl_name [, tbl_name] ...] [NOWAIT | SKIP LOCKED]
				 * 			| LOCK IN SHARE MODE]
				 * 		]
				 */
				/**
				 * table_references:
				 * 				escaped_table_reference [, escaped_table_reference] ...
				 * escaped_table_reference:
				 * 				table_reference
				 * 				| { OJ table_reference }
				 * table_reference:
				 * 				table_factor
				 * 				| joined_table
				 * table_factor:
				 * 				tbl_name [PARTITION (partition_names)]
				 * 				[[AS] alias] [index_hint_list]
				 * 				| table_subquery [AS] alias [(col_list)]
				 * 				| ( table_references )
				 * joined_table:
				 * 				table_reference {[INNER | CROSS] JOIN | STRAIGHT_JOIN} table_factor [join_specification]
				 * 				| table_reference {LEFT|RIGHT} [OUTER] JOIN table_reference join_specification
				 * 				| table_reference NATURAL [INNER | {LEFT|RIGHT} [OUTER]] JOIN table_factor
				 * join_specification:
				 * 				ON search_condition
				 * 				| USING (join_column_list)
				 * join_column_list:
				 * 				column_name [, column_name] ...
				 * index_hint_list:
				 * 				index_hint [, index_hint] ...
				 * index_hint:
				 * 				USE {INDEX|KEY}
				 * 					[FOR {JOIN|ORDER BY|GROUP BY}] ([index_list])
				 * 				| {IGNORE|FORCE} {INDEX|KEY}
				 * 					[FOR {JOIN|ORDER BY|GROUP BY}] (index_list)
				 * index_list:
				 * 				index_name [, index_name] ...
				 */
				$table =$this->getFormatName();
				$hasJoin =count($this->join)>0;
				$select =$this->buildSelect(!$hasJoin);
				if($hasJoin){
					list($join, $joinSelect) =$this->buildJoin($this->join);
					$select .=(''===$joinSelect) ?'' :($select?', ':'').$joinSelect;
				} else $join='';
				$where =$this->buildWhere($this->where);
				$group =$this->buildSort($this->group, 'GROUP');
				$having =$this->buildWhere($this->having, ' HAVING');
				$order =$this->buildSort($this->sort);
				$limit =$this->buildLimit($this->limit);
				$options =$this->buildOptions('DISTINCT,DISTINCTROW,HIGH_PRIORITY,STRAIGHT_JOIN,SQL_SMALL_RESULT,SQL_BIG_RESULT,SQL_BUFFER_RESULT,SQL_NO_CACHE,SQL_CALC_FOUND_ROWS', $this->options);
				return "SELECT{$options} {$select} FROM {$table}{$join}{$where}{$group}{$having}{$order}{$limit}";
			case 'do':
			default:
				/**
				 * 13.2.3 DO Syntax
				 * DO expr [, expr] ...
				 */
				return 'DO 1';
		}
	}
	protected function buildJoin($joins=[]){
		$_joins =[];
		$_select =[];
		foreach($joins as $join){
			if(empty($join[2])) $join[2]=['LEFT'];
			$options =$this->buildOptions('NATURAL,INNER,CROSS,LEFT,RIGHT', $join[2]);
			$table =$join[0]->getFormatName();
			$on =[];
			if(!is_array($join[1])) $join[1]=[$join[1]=>$join[1]];
			foreach($join[1] as $joinField =>$field){
				$j =$join[0]->formatField(empty($joinField) ?null :$joinField);
				$t =$this->formatField($field);
				$on[] ="{$j} = $t";
			}
			$keyword =in_array('STRAIGHT', $join[2]) ?'STRAIGHT_JOIN' :'JOIN';
			$_on =implode(', ', $on);
			$_joins[]="{$options} {$keyword} {$table} ON ({$_on})";

			$select =$join[0]->buildSelect(false);
			if(''!==$select) $_select[] =$select;
		}
		if(count($_select)){
			$select =implode(", ", $_select);
		} else $select ='';
		return [implode("", $_joins), $select];
	}
	protected function buildOptions($in='', $options=[]){
		if(!is_array($options)) return '';
		$r=[];
		foreach(explode(',', $in) as $opt){
			if(in_array($opt, $options)) $r[] =$opt;
		}
		return count($r) ?' '.implode(' ', $r):'';
	}
	public function __invoke($value):sql\part{
		return new sql\part($value, 'value', $this);
	}
	/**
	 * @param $name
	 * @param $arguments
	 * @return \nx\helpers\db\sql\part
	 */
	public static function __callStatic($name, $arguments):sql\part{
		return (new sql\part($name, 'function'))->arguments(...$arguments);
	}
	//-------------------------------------------------------------------------------------------------------------
	public function offsetSet($offset, $value):void{
		//
	}
	public function offsetExists($offset):bool{
		//
	}
	public function offsetUnset($offset):void{
		//
	}
	/**
	 * @param mixed $offset
	 * @return \nx\helpers\db\sql\part
	 */
	public function offsetGet($offset):sql\part{
		return new sql\part($offset, 'field',$this);
	}
}