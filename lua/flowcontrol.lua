-- if
op = "*"
a, b = 1, 2

if op == "+" then
    r = a + b
elseif op == "-" then
    r = a - b
elseif op == "*" then
    r = a * b
elseif op == "/" then
    r = a / b
else 
    error("invalid operation")

end
print(r)

-- while
local i = 1
a = {1, 2, 3, 4, 5}
while a[i] do
    print(a[i])
    i = i + 1
end

-- repeat
a = {1, 2, 3, 4, 5}
i = #a
repeat
    print(a[i])
    i = i - 1
until i < 1

-- 数字型 for
a = {1, 2, 3, 4, 5}
for i = 1, #a, 1 do
    print(a[i])
end

tmp = nil
for i = 1, #a, 1 do
    if a[i] == 3 then
        tmp = i
    end
end
print(tmp)


--泛型 for
print("泛型 for")
a = {"a", "b", "c", "d", "e", "f"}
for k in pairs(a) do
    print(k)
end

for k, v in pairs(a) do
    print(k, v)
end

for k, v in ipairs(a) do
    print(k, v)
end
