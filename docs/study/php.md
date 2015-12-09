php所有的变量都存在一个zval的结构里面，通过refcount和is_ref来存储变量的引用关系。
refcount是变量的引用次数，is_ref是变量是否被引用，当is_ref=0的时候refcount总是为1。
旧版的GC策略是refcount=0的时候，php执行垃圾回收。
但有一种特殊情况是数组的某一个元素指向自己的时候，unset数组，refcount仍然为1，会造成内存泄露。
新版的GC策略是如果一个zval的refcount减少之后>0，那么此zval有可能成为垃圾，GC将其放入一个缓冲区，
当缓冲区满了之后，GC执行垃圾回收，用深度优先算法对每一个zval执行减1操作，
再用深度优先判断每一个zval的refcount，如果==0，标记为垃圾，如果>0，执行+1还原操作。遍历zval将垃圾释放。

多进程读写文件
<?php
//写文件
$f = fopen('1.txt', 'a+');
flock($f, LOCK_EX);
fwrite($f, 'hello');
flock($f, LOCK_UN);
fclose($f);

//读文件
$f = fopen('1.txt', 'r+');
flock($f, LOCK_SH);
while(!feof($f)){
	$c.=fread($f, 8192);
}

flock($f, LOCK_UN);
var_dump($c);
fclose($f);


按位与,按位异或,按位取反

**& 按位与，相同的不变，否则都算成0  
| 按位或，  
^ 按位异或，不相同的都算成1**  
PHP按位与或 (^ 、&)运算也是很常用的逻辑判断类型，有许多的PHP新手们或许对此并不太熟悉，今天结合一些代码对PHP与或运算做些介绍，先说明下，在PHP中，按位与主要是对二进制数操作：

    <?php
    $a = 1;
    $b = 2;
    $c = $a^b;
    echo $c // 3
    ?>

十进制1换算成二进制为：00000001
十进制2换算成二进制为：00000010
按位^ 00000011，就是把不相同的都算成1，然后：
    
    <?php
    $a = 1;
    $b = 2;
    echo $a & $c; // 1
    ?>

十进制3换算成二进制为：00000011
十进制1换算成二进制为：00000001
按位& 00000001，就是各个位数相同的不变，否则都算成0，按位“&”后返回值是没意义的，主要是用来判断$a 是否存在于 $c，权限用法比较多：

    <?php
    $my_privilege = 15; // 1+2+4+8 拥有全部权限
    $Pri = '';
    $privilege_arr = array(8=>'增', 4=>'删',2=>'改',1=>'查');
    foreach($privilege_arr as $k =>$v){
    $k & $my_privilege && $Pri .= '我有'.$v.'的权力<br>';
    }
    echo $Pri;
    ?>

通过一个值就可以区分出很多字段  
> $a = hexdec('0x10c04000');  
> $b = ($a & 0x0FF00000) >20;  
> $c = ($a & 0x000FF000) >12;  
> var_dump($a, $b, $c);  
> 输出：  
> int 281034752  
> int 12  
> int 4  
