print(type("hello world"))
print(type(10.4*3))
print(type(print))
print(type(type))
print(type(true))
print(type(nil))
print(type({}))

a = {}
a["x"] = 10
print(a.x)
print(#a)

b = {}
b[#b + 1] = 1
b[1000] = 1
print(#b, table.maxn(b))
