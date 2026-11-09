--创建一个全局变量
v1 = "hello"
print(v1, v2)

--删除一个全局变量
v1 = nil
print(v1, v2)

--字符串中的数值转义
a = "alo123"
b = "\97lo\04923"
print(a)
print(b)

--多行字符串和字符连接
a = [[
<html>
<head>
</head>
]]

b = "<body></body>\n</html>"
print(a .. b)

--字符串长度
a = "hello"
print(#a) -- k

--字符类型转换
print(type(tonumber("10"))) -- number
print(type(tostring(10))) -- string

--构造table
a = {}
a["h"] = "hello"
a[20] = "world"
print(a.h, a[20])

--获取table 长度
a = {"a", "b", "c"}
print(#a) -- 3
a[#a] = nil -- 删除最后一个值
a[#a + 1] = "e" -- 添加一个元素
print(#a)

--间隙数组
t = {}
t[1000] = 1
print(#t) -- 0
print(table.maxn(t)) -- 1000
