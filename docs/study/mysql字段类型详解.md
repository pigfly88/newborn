bigint：2^63
int：2^31
mediumint：2^23
smallint：2^15
tinyint：2^8
Type	Storage	Minimum Value	Maximum Value
 	(Bytes)	(Signed/Unsigned)	Signed/Unsigned)
TINYINT	1	-128	127
 	 	0	255
SMALLINT	2	-32768	32767
 	 	0	65535
MEDIUMINT	3	-8388608	8388607
 	 	0	16777215
INT	4	-2147483648	2147483647
 	 	0	4294967295
BIGINT	8	-9223372036854775808	9223372036854775807
 	 	0	18446744073709551615
如果是unsigned则只有正数，大小增大一倍。