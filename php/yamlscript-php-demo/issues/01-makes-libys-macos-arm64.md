<!--
Repository: https://github.com/makeplus/makes/issues/new
Title (copy the H1 line below): libys.mk: wrong macOS arm64 asset name (macos-aarch64) causes a 404 on install
-->

# libys.mk: wrong macOS arm64 asset name (macos-aarch64) causes a 404 on install

## Environment

- macOS 14.6.1 (23G93), arm64 (Apple Silicon)
- in-1 `v0.1.0-49-g2f4d316` (`source <(curl -sL https://in-1.cc) --local libys`)
- makes `d8f626f` ("Update versions")
- Install prefix: `~/.local` (`--local`)

## Steps to reproduce

```bash
source <(curl -sL https://in-1.cc) --local libys
```

## Actual result

```
… libys v0.3.7 installing
X libys v0.3.7 NOT installed (14.00s)
* Installing 'libys' locally
curl+ https://github.com/yaml/yamlscript/releases/download/0.3.7/libys-0.3.7-macos-aarch64.tar.xz > ~/.local/share/libys/0.3.7/cache/libys-0.3.7-macos-aarch64.tar.xz
curl: (22) The requested URL returned error: 404
curl+ failed to fetch 'https://github.com/yaml/yamlscript/releases/download/0.3.7/libys-0.3.7-macos-aarch64.tar.xz' (try 1 of 5); retrying
... (tries 2-5, same 404) ...
curl+ failed to fetch 'https://github.com/yaml/yamlscript/releases/download/0.3.7/libys-0.3.7-macos-aarch64.tar.xz' after 5 tries
make: *** [/Users/<user>/.local/share/libys/0.3.7/cache/libys-0.3.7-macos-aarch64.tar.xz] Error 1
X Full log: /Users/<user>/.local/share/in-1/local/log/libys.log
```

## Root cause

`libys.mk` line ~10:

```make
OA-linux-arm64 := linux-aarch64
OA-linux-int64 := linux-x64
OA-macos-arm64 := macos-aarch64
OA-macos-int64 := macos-x64
```

The yamlscript 0.3.7 release assets for `libys` are:

```
libys-0.3.7-freebsd-x64.tar.xz
libys-0.3.7-linux-aarch64.tar.xz
libys-0.3.7-linux-x64.tar.xz
libys-0.3.7-macos-arm64.tar.xz     <-- macOS asset uses "arm64"
libys-0.3.7-macos-x64.tar.xz
libys-0.3.7-standalone.jar
libys-0.3.7-windows-arm64.zip
libys-0.3.7-windows-x64.zip
```

There is no `-macos-aarch64` asset, so the download always 404s. `linux-aarch64` is correct (the Linux assets really do use `aarch64`), so the macOS line looks like it was copied from the Linux one.

Note that `yamlscript.mk` (the `ys` tool) in this same repo already gets it right:

```make
OA-macos-arm64 := macos-arm64
```

## Suggested fix

```make
OA-macos-arm64 := macos-arm64
```

## Verification

in-1 passes `NAME=VALUE` arguments through to makes, so the fix can be verified without editing the clone:

```bash
source <(curl -sL https://in-1.cc) --local libys OA-macos-arm64=macos-arm64
```

With that override the tarball downloads and unpacks correctly. The install then stops at a separate upstream packaging bug in the yamlscript release tarball (installed `libys.dylib*` are dangling symlinks), which I am reporting separately at yaml/yamlscript#XXXX (fill in the number after filing that one first).
