@extends('layouts.app')

@section('content')
<div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
  <div class="card-body px-4 py-3">
    <div class="row align-items-center">
      <div class="col-9">
        <h4 class="fw-semibold mb-8">Manajemen Log</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="/">Dashboard</a></li>
            <li class="breadcrumb-item" aria-current="page">God Mode</li>
            <li class="breadcrumb-item" aria-current="page">Manajemen Log</li>
          </ol>
        </nav>
      </div>
      <div class="col-3">
        <div class="text-center mb-n5">
          <i class="ti ti-history fs-8 text-info"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <h5 class="card-title fw-semibold">Daftar Log</h5>
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="ti ti-plus"></i> Tambah Log Manual
      </button>
    </div>

    <div class="table-responsive">
      <table id="dataTable" class="table border table-striped table-bordered text-nowrap">
        <thead>
          <tr>
            <th>ID Log</th>
            <th>Pengajuan (Nomor / Perihal)</th>
            <th>Posisi</th>
            <th>Jabatan</th>
            <th>Status</th>
            <th>Tanggal Posisi</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach($logs as $log)
          <tr>
            <td>{{ $log->id_log }}</td>
            <td>
              @if($log->pengajuan)
                <strong>{{ $log->pengajuan->nomor_surat }}</strong><br>
                <small class="text-muted">{{ Str::limit($log->pengajuan->perihal, 30) }}</small>
              @else
                <span class="text-danger">Pengajuan Tidak Ditemukan</span>
              @endif
            </td>
            <td>{{ $log->posisi }}</td>
            <td>{{ $log->jabatan }}</td>
            <td>
              @php
                $color = 'primary';
                if(in_array($log->status, ['SELESAI', 'FINAL', 'ACC KABID'])) $color = 'success';
                if($log->status == 'REVISI') $color = 'danger';
              @endphp
              <span class="badge bg-{{ $color }}">{{ $log->status }}</span>
            </td>
            <td>{{ \Carbon\Carbon::parse($log->tanggal_posisi)->translatedFormat('d M Y, H:i:s') }}</td>
            <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $log->id_log }}">
                <i class="ti ti-edit"></i> Edit
              </button>
              <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalHapus{{ $log->id_log }}">
                <i class="ti ti-trash"></i> Hapus
              </button>
            </td>
          </tr>

          <!-- Modal Edit -->
          <div class="modal fade" id="modalEdit{{ $log->id_log }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <form action="{{ route('manajemen-log.update', $log->id_log) }}" method="POST">
                  @csrf
                  @method('PUT')
                  <div class="modal-header">
                    <h5 class="modal-title">Edit Log #{{ $log->id_log }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label class="form-label">Pengajuan ID</label>
                      <select class="form-select" name="id_pengajuan" required>
                        @foreach($pengajuans as $p)
                          <option value="{{ $p->id_pengajuan }}" {{ $log->id_pengajuan == $p->id_pengajuan ? 'selected' : '' }}>
                            {{ $p->id_pengajuan }} - {{ $p->nomor_surat }}
                          </option>
                        @endforeach
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Posisi</label>
                      <input type="text" class="form-control" name="posisi" value="{{ $log->posisi }}" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Jabatan</label>
                      <input type="text" class="form-control" name="jabatan" value="{{ $log->jabatan }}" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Status</label>
                      <input type="text" class="form-control" name="status" value="{{ $log->status }}" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Catatan</label>
                      <textarea class="form-control" name="catatan" rows="3">{{ $log->catatan }}</textarea>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Tanggal Posisi</label>
                      <input type="datetime-local" class="form-control" name="tanggal_posisi" value="{{ date('Y-m-d\TH:i', strtotime($log->tanggal_posisi)) }}" required>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- Modal Hapus -->
          <div class="modal fade" id="modalHapus{{ $log->id_log }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <form action="{{ route('manajemen-log.destroy', $log->id_log) }}" method="POST">
                  @csrf
                  @method('DELETE')
                  <div class="modal-header">
                    <h5 class="modal-title text-danger">Hapus Log</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    Apakah Anda yakin ingin menghapus log ID <strong>{{ $log->id_log }}</strong> secara permanen? Tindakan ini dapat mempengaruhi riwayat perjalanan dokumen.
                  </div>
                  <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger">Hapus Permanen</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('manajemen-log.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Tambah Log Manual</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Pengajuan ID</label>
            <select class="form-select" name="id_pengajuan" required>
              <option value="">-- Pilih Pengajuan --</option>
              @foreach($pengajuans as $p)
                <option value="{{ $p->id_pengajuan }}">
                  {{ $p->id_pengajuan }} - {{ $p->nomor_surat }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Posisi</label>
            <input type="text" class="form-control" name="posisi" placeholder="Contoh: DIKDASMEN" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Jabatan</label>
            <input type="text" class="form-control" name="jabatan" placeholder="Contoh: administrator" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <input type="text" class="form-control" name="status" placeholder="Contoh: DALAM PROSES, SELESAI" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Catatan</label>
            <textarea class="form-control" name="catatan" rows="3"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Tanggal Posisi</label>
            <input type="datetime-local" class="form-control" name="tanggal_posisi" value="{{ date('Y-m-d\TH:i') }}" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Log</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  $(document).ready(function() {
    if (!$.fn.DataTable.isDataTable('#dataTable')) {
        $('#dataTable').DataTable({
          "language": {
            "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
          },
          "order": [[ 0, "desc" ]] // Default order by ID DESC
        });
    }
  });
</script>
@endpush
