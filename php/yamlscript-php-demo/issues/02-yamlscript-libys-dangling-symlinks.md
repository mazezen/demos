<!--
Repository: https://github.com/yaml/yamlscript/issues/new
Title (copy the H1 line below): libys tarballs: install Makefile copies only the libys.so*/libys.dylib* symlinks, not the real libys-glojure library
Related: #267 (same install path, previous Linux-glob bug)
-->

# libys tarballs: install Makefile copies only the libys.so*/libys.dylib* symlinks, not the real libys-glojure library

## Environment

- yamlscript / libys 0.3.7
- Reproduced on macOS 14.6.1 arm64 (`libys-0.3.7-macos-arm64.tar.xz`), and with the
  `libys-0.3.7-macos-x64.tar.xz` and `libys-0.3.7-linux-x64.tar.xz` tarballs (contents + install).

## Steps to reproduce

```bash
curl -sLO https://github.com/yaml/yamlscript/releases/download/0.3.7/libys-0.3.7-macos-arm64.tar.xz
tar -xJf libys-0.3.7-macos-arm64.tar.xz
make -C libys-0.3.7-macos-arm64 install PREFIX=$PWD/prefix
ls -l prefix/lib
file prefix/lib/libys.dylib.0.3.7
```

## Actual result

`make install` reports success, but only symlinks are installed:

```
mkdir -p /tmp/.../prefix/lib
cp -pP libys.dylib libys.dylib.0 libys.dylib.0.3.7 /tmp/.../prefix/lib/
mkdir -p /tmp/.../prefix/include/libys-0.3.7
cp -pP *.h /tmp/.../prefix/include/libys-0.3.7/
Installed /tmp/.../prefix/lib/libys.dylib - version 0.3.7
```

```
prefix/lib:
libys.dylib       -> libys-glojure.dylib
libys.dylib.0     -> libys-glojure.dylib
libys.dylib.0.3.7 -> libys-glojure.dylib
```

```
$ file prefix/lib/libys.dylib.0.3.7
prefix/lib/libys.dylib.0.3.7: broken symbolic link to libys-glojure.dylib
```

All three installed entries are dangling: the real 24 MB library (`libys-glojure.dylib`)
is never copied. Anything that links `-libys` and loads it at runtime fails, even though the
install claims success.

The tarball itself is fine — the problem is only in the bundled Makefile:

```
$ tar -tvJf libys-0.3.7-macos-arm64.tar.xz | grep dylib
-rw-r--r--  ...  24554450  libys-0.3.7-macos-arm64/libys-glojure.dylib
lrwxr-xr-x  ...         0  libys-0.3.7-macos-arm64/libys.dylib -> libys-glojure.dylib
lrwxr-xr-x  ...         0  libys-0.3.7-macos-arm64/libys.dylib.0 -> libys-glojure.dylib
lrwxr-xr-x  ...         0  libys-0.3.7-macos-arm64/libys.dylib.0.3.7 -> libys-glojure.dylib
```

Same on Linux (`libys-0.3.7-linux-x64.tar.xz`):

```
-rw-r--r--  ...  28849768  libys-glojure.so
lrwxrwxrwx  ...         0  libys.so -> libys-glojure.so
lrwxrwxrwx  ...         0  libys.so.0 -> libys-glojure.so
lrwxrwxrwx  ...         0  libys.so.0.3.7 -> libys-glojure.so
```

and `make install` there installs three dangling links as well.

## Root cause

Makefile inside every libys tarball:

```make
LIBYS := $(firstword $(wildcard libys.so* libys.dylib* libys.dll))
LIBYS-FILES := $(wildcard libys.so* libys.dylib* libys.dll)
...
else ifneq (,$(LIBYS))
	mkdir -p $(PREFIX)/lib
	cp -pP $(LIBYS-FILES) $(PREFIX)/lib/
```

`libys.so*` / `libys.dylib*` only match the versioned symlinks; the actual library is named
`libys-glojure.<so|dylib>` and is not matched by either wildcard, so `$(LIBYS-FILES)` never
contains it. `cp -pP` preserves the symlinks as symlinks, so `PREFIX/lib` ends up with links
whose target was never installed.

This is a follow-up to #267: that fix replaced the Linux-only `cp libys*.so*` glob with
`$(LIBYS-FILES)`, but the new glob still does not cover the symlink target.

## Suggested fix

Minimal fix — install the resolved targets alongside the links:

```make
else ifneq (,$(LIBYS))
	mkdir -p $(PREFIX)/lib
	cp -pP $(LIBYS-FILES) $(sort $(realpath $(LIBYS-FILES))) $(PREFIX)/lib/
```

Better long-term fix — ship the real library under the soname and keep the GraalVM name as a
compatibility symlink in the tarball, e.g.

```
libys.dylib.0.3.7            (real file)
libys.dylib.0, libys.dylib   (links)
libys-glojure.dylib          (link, compatibility)
```

Then the current `cp -pP $(LIBYS-FILES)` installs a complete set, `-libys` /
`-l:libys.dylib.0.3.7` resolve, and `libys-glojure.*` keeps working for anything that
still names the GraalVM artifact.

## Workaround

Copy the real library into the prefix after installing:

```bash
make -C libys-0.3.7-macos-arm64 install PREFIX=$PWD/prefix
cp libys-0.3.7-macos-arm64/libys-glojure.dylib prefix/lib/
test -s prefix/lib/libys.dylib.0.3.7 && echo ok
```

## How it was found (impact)

Installing libys through in-1 (`source <(curl -sL https://in-1.cc) --local libys`) on
macOS arm64. in-1 verifies the installed library artifact non-empty
(`[[ -s $artifact ]]` in `bin/in-1`, ~L1300), so after a "successful" make the install aborts:

```
X Required library '/Users/<user>/.local/share/libys/0.3.7/lib/libys.dylib.0.3.7' was not installed
```

Because the tarball's `install` target touches `libys.dylib.0.3.7` as its success marker, any
consumer that only checks for the file's existence (rather than that it resolves to a real
library) will silently accept a broken install.
