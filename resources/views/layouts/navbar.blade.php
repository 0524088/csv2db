    <nav class="navbar navbar-expand-lg navbar-light bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand text-light ms-2" href="index">首頁</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link text-light ms-3 active" href="upload">上傳</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light ms-3" href="chart">圖表</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-light ms-3" href="logout" onclick="logout();">登出</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <script>
        function logout() {
            let url = `${server_url}/logout`
            let headers = {
                "Content-Type": "application/json",
                "Accept": "application/json",
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
            fetch(url, {
                method: 'get',
                headers: headers,
            })
            .then(response => response.json())
            .then((data) => {
                window.location.href = "index";
            })
            .catch((error) => {
                alert(error);
            });
        }
    </script>