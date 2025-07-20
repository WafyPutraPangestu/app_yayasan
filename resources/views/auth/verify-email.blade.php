<x-layout>
  <div class="container" style="max-width: 600px; margin-top: 50px; text-align: center;">

    <h2>Verifikasi Alamat Email Anda</h2>

    <p>
      Terima kasih telah mendaftar! Sebelum melanjutkan, bisakah Anda memverifikasi alamat email Anda dengan mengklik link yang baru saja kami kirimkan?
    </p>
    <p>
      Jika Anda tidak menerima email, kami akan dengan senang hati mengirimkan yang lain.
    </p>

    @if (session('message'))
    <div class="alert alert-success" style="color: green; margin-bottom: 20px;">
      {{ session('message') }}
    </div>
    @endif

    <div style="display: flex; justify-content: center; gap: 20px;">
      <!-- Form untuk mengirim ulang email verifikasi -->
      <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn btn-primary">
          Kirim Ulang Email Verifikasi
        </button>
      </form>

      <!-- Form untuk logout -->
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-secondary">
          Logout
        </button>
      </form>
    </div>

  </div>
</x-layout>