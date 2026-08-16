@extends('layouts.app')

@section('content')
<div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
  <div class="card-body px-4 py-3">
    <div class="row align-items-center">
      <div class="col-9">
        <h4 class="fw-semibold mb-8">Tambah Pengajuan Baru</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="/">Dashboard</a></li>
            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="{{ route('pengajuan.index') }}">Pengajuan</a></li>
            <li class="breadcrumb-item" aria-current="page">Tambah</li>
          </ol>
        </nav>
      </div>
      <div class="col-3">
        <div class="text-center mb-n5">
          <i class="ti ti-file-plus fs-8 text-info"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <h5 class="mb-4 fw-semibold">Form Pengajuan Dokumen</h5>
    <form action="{{ route('pengajuan.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
      <div class="row">
        <!-- Pengirim (Readonly) -->
        <div class="col-md-6 mb-3">
          <label class="form-label">Lembaga Pengirim</label>
          <input type="text" class="form-control" value="{{ Auth::user()->lembaga->nama_lembaga ?? 'Tidak Terkait Lembaga' }}" disabled readonly>
          <small class="text-muted">Data ditarik otomatis dari profil Anda.</small>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Nomor Surat <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="nomor_surat" required placeholder="Contoh: 001/MTS/VIII/2024">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Jenis Surat <span class="text-danger">*</span></label>
          <select class="form-select" name="jenis_surat" required>
            <option value="">-- Pilih Jenis Surat --</option>
            @foreach($jenisSurats as $js)
              <option value="{{ $js->nama_jenis }}">{{ $js->nama_jenis }}</option>
            @endforeach
          </select>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Tujuan Jabatan <span class="text-danger">*</span></label>
          <select class="form-select" name="tujuan" required>
            <option value="">-- Pilih Tujuan --</option>
            <option value="Pengasuh">Pengasuh</option>
            <option value="Ketua Yayasan">Ketua Yayasan</option>
            <option value="BPK2M">BPK2M</option>
            <option value="Bendahara">Bendahara</option>
            <option value="Kabid DIKDASMEN">Kabid DIKDASMEN</option>
          </select>
        </div>

        <div class="col-md-12 mb-3">
          <label class="form-label">Perihal <span class="text-danger">*</span></label>
          <input type="text" class="form-control" name="perihal" required placeholder="Perihal surat pengajuan...">
        </div>

        <div class="col-md-6 mb-4">
          <label class="form-label">File 1 (Pengantar) <span class="text-danger">*</span></label>
          <input class="form-control" type="file" name="file1" accept=".pdf" required>
          <small class="text-muted">Maksimal 10 MB, wajib format PDF.</small>
        </div>

        <div class="col-md-6 mb-4">
          <label class="form-label">File 2 (Lampiran) <span class="text-danger">*</span></label>
          <input class="form-control" type="file" name="file2" accept=".pdf" required>
          <small class="text-muted">Maksimal 10 MB, wajib format PDF.</small>
        </div>

      </div>

      <div class="d-flex justify-content-end gap-2 mt-3">
        <a href="{{ route('pengajuan.index') }}" class="btn btn-outline-danger">Batal</a>
        <button type="submit" class="btn btn-primary"><i class="ti ti-send"></i> Kirim Pengajuan</button>
      </div>
    </form>
  </div>
</div>
@endsection
