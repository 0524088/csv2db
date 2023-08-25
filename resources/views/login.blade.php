@extends('layouts.base')
@section('title', '登入')
@section('content')
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

        document.addEventListener("keypress", function(event) {
            if (event.key === "Enter") {
                login();
            }
        });

        function login() {
            document.getElementById('msg-alert').innerHTML = '';
            let ac = document.getElementById('input-account').value ;
            let pw = document.getElementById('input-password').value ;
            if(ac === '') { document.getElementById('msg-alert').innerHTML = '請輸入帳號'; return; }
            if(pw === '') { document.getElementById('msg-alert').innerHTML = '請輸入密碼'; return; }

            let headers = {
                "Content-Type": "application/json",
                "Accept": "application/json",
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
            let body = {
                'account' : ac,
                'password' : pw
            }
            
            // fetch version
            fetch('login', {
                method: 'post',
                headers: headers,
                body: JSON.stringify(body)
            })
            .then(response => response.json())
            .then((data) => {
                if(data.status === 'error') document.getElementById('msg-alert').innerHTML = data.message;
                else {
                    console.log(data.message);
                    window.location.href = "index";
                }
            })
            .catch((error) => {
                alert(error);
            });

            /*// xmlhttprequest version
            let xhr = new XMLHttpRequest();
            xhr.open('post', url, true);
            xhr.setRequestHeader("Content-Type", "application/json");
            xhr.setRequestHeader("Accept", "application/json");
            xhr.setRequestHeader("X-CSRF-TOKEN", document.querySelector('meta[name="csrf-token"]').getAttribute('content'));

            xhr.send(JSON.stringify(body));
            
            xhr.onload = function() {
                let data = JSON.parse(this.responseText);

                if(data.status === 'error') document.getElementById('msg-alert').innerHTML = data.message;
                else {
                    console.log(data.message);
                    window.location.href = "index";
                }
            };*/
        }
    </script>
@endsection