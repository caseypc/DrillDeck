<div class="card mt-3"><div class="card-body p-3">
    <h3 class="h6">Photograph this bowling ball</h3>
    <p class="small text-secondary">Take a photo with a phone or upload an existing image. This image is stored privately and shown as a photographic reference, not as a calibrated 3D texture.</p>
    @if($ball->photo_path)
        <img src="{{ route('shop.balls.photo',$ball) }}" alt="Photo of {{ $ball->ball_name }}" class="img-fluid rounded mb-3" style="max-height:280px;object-fit:contain;">
    @endif
    <form method="POST" action="{{ route('shop.balls.photo.upload',$ball) }}" enctype="multipart/form-data">
        @csrf <label class="form-label" for="ball-photo">Ball photo (JPEG, PNG, WebP)</label>
        <input class="form-control mb-2" id="ball-photo" type="file" name="photo" accept="image/jpeg,image/png,image/webp" capture="environment" required>
        <button class="btn btn-outline-primary w-100">Upload ball photo</button>
    </form>
</div></div>
