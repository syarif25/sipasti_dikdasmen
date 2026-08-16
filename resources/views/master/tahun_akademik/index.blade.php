@extends('layouts.app')

@section('content')
<div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
  <div class="card-body px-4 py-3">
    <div class="row align-items-center">
      <div class="col-9">
        <h4 class="fw-semibold mb-8">Data Tahun Akademik</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="/">Dashboard</a></li>
            <li class="breadcrumb-item" aria-current="page">Master Data</li>
            <li class="breadcrumb-item" aria-current="page">Tahun Akademik</li>
          </ol>
        </nav>
      </div>
      <div class="col-3">
        <div class="text-center mb-n5">
          <i class="ti ti-calendar fs-8 text-info"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <div class="d-flex align-items-center justify-content-between mb-4">
      <h5 class="card-title fw-semibold">Daftar Tahun Akademik</h5>
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
        <i class="ti ti-plus"></i> Tambah Data
      </button>
    </div>

    <div class="table-responsive">
      <table id="dataTable" class="table border table-striped table-bordered text-nowrap">
        <thead>
            <tr>
            <th>No</th>
            <th>Tahun Akademik</th>
            <th>Status</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach($tahun_akademiks as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item->tahun_akademik }}</td>
            <td>
              <span class="badge bg-{{ $item->status == 'Aktif' ? 'success' : 'danger' }} rounded-3 fw-semibold">
                {{ $item->status }}
              </span>
            </td>
            <td>
              @if($item->status != 'Aktif')
              <form action="{{ route('master.tahun-akademik.activate', $item->id_tahun) }}" method="POST" class="d-inline form-activate">
                @csrf
                @method('PATCH')
                <button type="button" class="btn btn-sm btn-success btn-activate">
                  <i class="ti ti-check"></i> Aktifkan
                </button>
              </form>
              @endif
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $item->id_tahun }}">
                <i class="ti ti-edit"></i> Edit
              </button>
              <form action="{{ route('master.tahun-akademik.destroy', $item->id_tahun) }}" method="POST" class="d-inline form-delete">
                @csrf
                @method('DELETE')
                <button type="button" class="btn btn-sm btn-danger btn-delete">
                  <i class="ti ti-trash"></i> Hapus
                </button>
              </form>
            </td>
          </tr>

          <!-- Modal Edit -->
          <div class="modal fade" id="modalEdit{{ $item->id_tahun }}" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <form action="{{ route('master.tahun-akademik.update', $item->id_tahun) }}" method="POST">
                  @csrf
                  @method('PUT')
                  <div class="modal-header">
                    <h5 class="modal-title" id="modalEditLabel">Edit Tahun Akademik</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label class="form-label">Tahun Akademik</label>
                      <input type="text" class="form-control" name="tahun_akademik" value="{{ $item->tahun_akademik }}" required maxlength="20">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Status</label>
                      <select name="status" class="form-select" required>
                        <option value="Aktif" {{ $item->status == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Tidak Aktif" {{ $item->status == 'Tidak Aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                      </select>
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
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('master.tahun-akademik.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="modalTambahLabel">Tambah Tahun Akademik</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Tahun Akademik</label>
            <input type="text" class="form-control" name="tahun_akademik" required maxlength="20">
            <small class="text-muted">Contoh: 2025/2026</small>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" required>
              <option value="Aktif">Aktif</option>
              <option value="Tidak Aktif">Tidak Aktif</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    if ($.fn.DataTable.isDataTable('#dataTable')) {
        $('#dataTable').DataTable().destroy();
    }
    $('#dataTable').DataTable({
      "language": {
        "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
      }
    });

    $('.btn-delete').on('click', function(e) {
      e.preventDefault();
      let form = $(this).closest('form');
      Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data tahun akademik ini akan dihapus permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      })
    });

    $('.btn-activate').on('click', function(e) {
      e.preventDefault();
      let form = $(this).closest('form');
      Swal.fire({
        title: 'Aktifkan Tahun Akademik?',
        text: "Tahun akademik lainnya akan otomatis dinonaktifkan.",
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Ya, Aktifkan!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      })
    });
  });
</script>
@endpush
