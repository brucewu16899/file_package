<!DOCTYPE html>
<html>
    <head>
        <title>Laravel</title>

        <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>

        <style>
            html, body {
                height: 100%;
            }

            body {
                margin: 0;
                padding: 0;
                width: 100%;
                display: table;
                font-weight: 100;
                font-family: 'Lato';
            }

            .container {
                text-align: center;
                display: table-cell;
                vertical-align: middle;
            }

            .content {
                text-align: center;
                display: inline-block;
            }

            .title {
                font-size: 96px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="content">
                <div class="title">{{$msg}}</div>
                <div>
                    <h2>檔案列表</h2>
                    @if (session('result_msg') != '')
                        <div class="alert alert-success">
                            {{ session('result_msg') }}
                        </div>
                    @endif
                    @foreach($show_name as $key=>$val)
                        <p><a href="{{asset('file/download?id=').$key}}">{{$val}}</a>　|  <button type="button" onclick="del({{$key}})">刪除</button></p>
                    @endforeach
                </div>
                <div>
                    <h2>檔案上傳</h2>
                    <form method="post" action="{{asset('file/upload')}}" enctype="multipart/form-data">
                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                        <input type="file" name="upload_file[]">
                        <input type="file" name="upload_file[]">
                        <input type="file" name="upload_file[]">
                        <br><br><br>
                        <input type="submit" value="送出">
                    </form>
                </div>
                <br><br>
                <div>
                    <h2>資料夾刪除</h2>
                    <form method="post" action="{{asset('file/deletefloder')}}">
                        <input type="hidden" name="_token" value="{{csrf_token()}}">
                        欲刪除之資料夾名稱:<input type="text" name="floder">
                        <br><br><br>
                        <input type="submit" value="送出">
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>

<script>
    function del(key){
        $.ajax({
            type: "POST",
            url: "{{asset('file/delete')}}",
            data: {
                _token: "{{ csrf_token() }}",
                key: key
            },
            dataType: "json",
            success: function (response) {
                var message='';
                for(var key in response){
                    message+=response[key].file_name+response[key].msg+'\n';
                }
                alert(message);
                location.reload();
            }
        });

    }

</script>