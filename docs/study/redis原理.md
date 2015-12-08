1.字符串SDS

	struct sdshdr {
	
	    // buf 已占用长度
	    int len;
	
	    // buf 剩余可用长度
	    int free;
	
	    // 实际保存字符串数据的地方
	    // 利用c99(C99 specification 6.7.2.1.16)中引入的 flexible array member,通过buf来引用sdshdr后面的地址，
	    // 详情google "flexible array member"
	    char buf[];
	};

redis的字符串结构是在C上面扩展的SDS，比起C字符串，SDS具有以下优点：

1. 获取字符串长度的时间复杂度为O(1)
2. 内存预分配，减少修改字符串的时候重复分配内存的次数
3. 不会内存溢出
4. 是二进制安全的，可以存储任何数据
5. 惰性内存释放