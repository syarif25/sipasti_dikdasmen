@extends('layouts.app')

@section('content')
<div class="card bg-info-subtle shadow-none position-relative overflow-hidden mb-4">
  <div class="card-body px-4 py-3">
    <div class="row align-items-center">
      <div class="col-9">
        <h4 class="fw-semibold mb-8">Manajemen Pengajuan</h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb">
            <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="/">Dashboard</a></li>
            <li class="breadcrumb-item" aria-current="page">Manajemen Pengajuan</li>
          </ol>
        </nav>
      </div>
      <div class="col-3">
        <div class="text-center mb-n5">
          <i class="ti ti-files fs-8 text-info"></i>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <h5 class="card-title fw-semibold mb-4">Daftar Pengajuan (Administrator)</h5>

    <div class="table-responsive">
      <table id="dataTable" class="table align-middle border text-nowrap table-striped table-bordered text-nowrap">
        <thead class="text-dark fs-3">
          <tr>
            <th>No.</th>
            <th>ID Pengajuan</th>
            <th>Tgl Upload</th>
            <th>Pengirim</th>
            <th>Nomor Surat</th>
            <th>Jenis Surat</th>
            <th>Perihal</th>
            <th>Tujuan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach($pengajuans as $item)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td class="fw-bold">{{ $item->id_pengajuan }}</td>
            <td>{{ \Carbon\Carbon::parse($item->tgl_upload)->format('d/m/Y') }}</td>
            <td>{{ $item->lembaga ? $item->lembaga->singkatan_lembaga : '-' }}</td>
            <td>{{ $item->nomor_surat }}</td>
            <td>{{ $item->jenis_surat }}</td>
            <td style="white-space: normal; min-width: 250px;">{{ $item->perihal }}</td>
            <td>{{ $item->tujuan }}</td>
            <td>
              <div class="d-flex gap-1">
                <button type="button" class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $item->id_pengajuan }}">
                  <i class="ti ti-edit"></i>
                </button>
                <form action="{{ route('manajemen-pengajuan.destroy', $item->id_pengajuan) }}" method="POST" class="delete-form">
                  @csrf
                  @method('DELETE')
                  <button type="button" class="btn btn-sm btn-danger btn-delete">
                    <i class="ti ti-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>

          <!-- Modal Edit -->
          <div class="modal fade" id="modalEdit{{ $item->id_pengajuan }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <form action="{{ route('manajemen-pengajuan.update', $item->id_pengajuan) }}" method="POST">
                  @csrf
                  @method('PUT')
                  <div class="modal-header">
                    <h5 class="modal-title">Edit Pengajuan Raw Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                  </div>
                  <div class="modal-body">
                    <div class="mb-3">
                      <label class="form-label">Nomor Surat</label>
                      <input type="text" class="form-control" name="nomor_surat" value="{{ $item->nomor_surat }}" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Perihal</label>
                      <input type="text" class="form-control" name="perihal" value="{{ $item->perihal }}" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Tujuan</label>
                      <input type="text" class="form-control" name="tujuan" value="{{ $item->tujuan }}" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Jenis Surat</label>
                      <select class="form-select" name="jenis_surat" required>
                        @foreach($jenisSurats as $js)
                          <option value="{{ $js->jenis_surat }}" {{ $item->jenis_surat == $js->jenis_surat ? 'selected' : '' }}>{{ $js->jenis_surat }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Lembaga Pengirim</label>
                      <select class="form-select" name="id_lembaga" required>
                        @foreach($lembagas as $lbg)
                          <option value="{{ $lbg->id_lembaga }}" {{ $item->id_lembaga == $lbg->id_lembaga ? 'selected' : '' }}>{{ $lbg->nama_lembaga }}</option>
                        @endforeach
                      </select>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Tahun Akademik</label>
                      <select class="form-select" name="id_tahun" required>
                        @foreach($tahunAkademiks as $ta)
                          <option value="{{ $ta->id_tahun }}" {{ $item->id_tahun == $ta->id_tahun ? 'selected' : '' }}>{{ $ta->tahun_akademik }}</option>
                        @endforeach
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

    // Handle delete with SweetAlert
    $('.btn-delete').on('click', function(e) {
      e.preventDefault();
      var form = $(this).closest('form');
      Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Data pengajuan akan dihapus permanen dari sistem!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if (result.isConfirmed) {
          form.submit();
        }
      });
    });
  });
</script>
@endpush
