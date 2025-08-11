import zipfile, os, pathlib

def make_zip(src_dir, out_zip):
    src_path = pathlib.Path(src_dir)
    with zipfile.ZipFile(out_zip, 'w', zipfile.ZIP_DEFLATED) as z:
        for path in src_path.rglob('*'):
            if path.is_file():
                rel = path.relative_to(src_path.parent)
                z.write(path, rel.as_posix())

if __name__ == "__main__":
    base = pathlib.Path(__file__).resolve().parent
    src = base
    out = base.parent / "brnews-pagebuilder-pro.zip"
    make_zip(src, out)
    print("OK ->", out)
