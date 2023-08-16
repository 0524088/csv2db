@extends('layouts.base')
@section('title', '登入')
@section('content')
    <style>
        .gradient-custom {
        /* fallback for old browsers */
        background: #6a11cb;
        /* Chrome 10-25, Safari 5.1-6 */
        background: -webkit-linear-gradient(to right, rgba(106, 17, 203, 1), rgba(37, 117, 252, 1));
        /* W3C, IE 10+/ Edge, Firefox 16+, Chrome 26+, Opera 12+, Safari 7+ */
        background: linear-gradient(to right, rgba(106, 17, 203, 1), rgba(37, 117, 252, 1))
        }
    </style>
    <div class="container py-5 h-100">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-12 col-md-8 col-lg-6 col-xl-5">
                <div class="card bg-dark text-white" style="border-radius: 1rem;">
                    <div class="card-body p-5 text-center">
                        <div class="mb-md-5 mt-md-4 pb-5">
                            <h2 class="fw-bold mb-2 text-uppercase">登入</h2>
                            <div class="form-outline form-white mb-4">
                                <input id="input-account" type="text" id="typeEmailX" class="form-control form-control-lg" />
                                <label class="form-label" for="typeEmailX">account</label>
                            </div>
                            <div class="form-outline form-white mb-4">
                                <input id="input-password" type="password" id="typePasswordX" class="form-control form-control-lg" />
                                <label class="form-label" for="typePasswordX">Password</label>
                            </div>

                            <div id="msg-alert" class="form-label" style="color: red"></div>

                            <button class="btn btn-outline-light btn-lg px-5" type="submit" id="btn-login" onclick="login();">登入</button>
                            <div class="d-flex justify-content-center text-center mt-4 pt-1">
                                <a href="#!" class="text-white"><i class="fab fa-facebook-f fa-lg"></i></a>
                                <a href="#!" class="text-white"><i class="fab fa-twitter fa-lg mx-4 px-2"></i></a>
                                <a href="#!" class="text-white"><i class="fab fa-google fa-lg"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        let btn = document.getElementById('btn-login');
        
        let url = `${server_url}/login`;


        function login() {
            document.getElementById('msg-alert').innerHTML = '';
            let ac = document.getElementById('input-account').value ;
            let pw = document.getElementById('input-password').value ;
            if(ac === '') { document.getElementById('msg-alert').innerHTML = '請輸入帳號'; return; }
            if(pw === '') { document.getElementById('msg-alert').innerHTML = '請輸入密碼'; return; }

            let headers = {
                "Content-Type": "application/json",
                "Accept": "application/json",
            }
            let body = {
                'account' : ac,
                'password' : pw
            }

            fetch(url, {
                method: 'post',
                headers: headers,
                body: JSON.stringify(body)
            })
            .then(response => response.json())
            .then((data) => {
                if(data.status === 'error') document.getElementById('msg-alert').innerHTML = data.message;
                else {
                    console.log('success');
                    window.location.href = "index";
                }
            })
            .catch((error) => {
                alert(error);
            });
        }
    </script>
@endsection