<div class="card-body">
    <img src="{{ asset($company->company_logo) }}" alt="Company Logo" class="d-block rounded" height="50" id="uploadedAvatar" /> <br>
    <div class="d-flex align-items-start align-items-sm-center gap-4" id="logo">
        <form action="{{ route('admin.company.logo.update') }}" method="post"
            enctype="multipart/form-data">
            @csrf
            @method('POST')
            <div class="button-wrapper">
                <label for="upload" class="btn btn-warning me-2 mb-4" tabindex="0">
                    <span class="d-none d-sm-block">Upload new logo</span>
                    <i class="bx bx-upload d-block d-sm-none"></i>
                    <input type="file" id="upload" name="company_logo" class="account-file-input" hidden accept="image/png" />
                </label>

                <button type="submit" class="btn btn-primary mb-4">
                    <i class="bx bx-reset d-block d-sm-none"></i>
                    <span class="d-none d-sm-block">Save</span>
                </button>

                <button type="button" class="btn btn-danger account-image-reset mb-4">
                    <i class="bx bx-reset d-block d-sm-none"></i>
                    <span class="d-none d-sm-block">Reset</span>
                </button>

                <p class="text-muted mb-0"><b class="text-danger">Allowed only PNG. Recommended size 192px / 36px</b></p>
            </div>
        </form>
    </div>
    <hr>
    {{-- Favicon Section --}}
    @if (session('verify') === 'company-favicon-updated')
        <div class="alert alert-{{ session('status') }} alert-dismissible text-dark" role="alert">
            <b>{{ session('message') }}</b>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <img src="{{ asset($company->company_favicon) }}" alt="Company Favicon" class="d-block rounded p-2" height="100" width="100" border="1px solid #000" id="uploadedFavicon" /> <br>
    <div class="d-flex align-items-start align-items-sm-center gap-4" id="favicon">
        <form action="{{ route('admin.company.favicon.update') }}" method="post"
            enctype="multipart/form-data">
            @csrf
            @method('POST')
            <div class="button-wrapper">
                <label for="upload_favicon" class="btn btn-warning me-2 mb-4" tabindex="0">
                    <span class="d-none d-sm-block">Upload new favicon</span>
                    <i class="bx bx-upload d-block d-sm-none"></i>
                    <input type="file" id="upload_favicon" name="company_favicon" class="favicon-file-input" hidden accept="image/png, image/ico" />
                </label>

                <button type="submit" class="btn btn-primary mb-4">
                    <i class="bx bx-reset d-block d-sm-none"></i>
                    <span class="d-none d-sm-block">Save</span>
                </button>

                <button type="button" class="btn btn-danger favicon-image-reset mb-4">
                    <i class="bx bx-reset d-block d-sm-none"></i>
                    <span class="d-none d-sm-block">Reset</span>
                </button>

                <p class="text-muted mb-0"><b class="text-danger">Allowed only PNG and ICO. Recommended size 100px / 100px</b></p>
            </div>
        </form>
    </div>
</div>
<hr class="my-0" />

<script>
    document.addEventListener('DOMContentLoaded', function (e) {
        // Logo preview functionality
        (function () {
            let logoImage = document.getElementById('uploadedAvatar');
            const logoInput = document.querySelector('.account-file-input'),
                logoReset = document.querySelector('.account-image-reset');

            if (logoImage && logoInput && logoReset) {
                const resetLogoImage = logoImage.src;
                logoInput.onchange = () => {
                    if (logoInput.files[0]) {
                        logoImage.src = window.URL.createObjectURL(logoInput.files[0]);
                    }
                };
                logoReset.onclick = () => {
                    logoInput.value = '';
                    logoImage.src = resetLogoImage;
                };
            }
        })();

        // Favicon preview functionality
        (function () {
            let faviconImage = document.getElementById('uploadedFavicon');
            const faviconInput = document.querySelector('.favicon-file-input'),
                faviconReset = document.querySelector('.favicon-image-reset');

            if (faviconImage && faviconInput && faviconReset) {
                const resetFaviconImage = faviconImage.src;
                faviconInput.onchange = () => {
                    if (faviconInput.files[0]) {
                        faviconImage.src = window.URL.createObjectURL(faviconInput.files[0]);
                    }
                };
                faviconReset.onclick = () => {
                    faviconInput.value = '';
                    faviconImage.src = resetFaviconImage;
                };
            }
        })();
    });
</script>