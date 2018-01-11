<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>测试接口</title>
</head>
<body>
<h1>测试检测接口</h1>

<table>
    @foreach($lists as $key=>$val)
        <tr>
            <td>{{ $val['name'] }}</td>
            <td><a href="upload/{{ $val['file'] }}" target="_blank"><img width="50" height="50" src="upload/{{ $val['file'] }}"></a></td>
            <td><a class="img" data-key="{{ $key }}" data-file="{{ $val['imei'] }}">识别</a> </td>
            <td id="res{{ $key }}">-</td>
        </tr>
    @endforeach
</table>
<script src="https://m.rekoon.cn/js/jquery-3.2.1.min.js"></script>
<script>
    $(function(){
        $('.img').on('click',function(){
            var key = $(this).data('key');
            var file = $(this).data('file');

            $.ajax({
                'url':'{{ url('imeis') }}',
                'type':'post',
                'data':{'file':file,'key':key},
                'success':function(res){
                    $('#res'+key).html(res.data);
                }
            })

        })
    })
</script>
</body>
</html>