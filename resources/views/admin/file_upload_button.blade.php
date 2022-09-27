<div class="form-group">
    <label for="title" class="col-sm-2  control-label">上传视频</label>
    <div class="col-sm-2">
        <input type="file" id="file" name="file"/>
        <div class="btn-group pull-left grid-create-btn" style="margin-top:10px;">
            <a href="javascript:;" class="btn btn-sm btn-success uploadFile" title="上传视频">
                <span class="hidden-xs">上传视频</span>
            </a>
        </div>
    </div>
    <div class="col-sm-4">
        <div id="wai">
            <div id="nei"></div>
        </div>
    </div>
</div>

<style>
    #wai {
        width: 200px;
        height: 20px;
        border: 1px solid green;
        display:none;
    }

    #nei {
        width: 0px;
        height: 20px;
        background: green;
        color: white;
        font-size: 14px;
        text-align: right;
    }
</style>
<script type="text/javascript">
    //获取文件分片对象
    const blobSlice = File.prototype.slice || File.prototype.mozSlice || File.prototype.webkitSlice;
    $(function () {
        //设置每个分片大小
        const chunkSize = 1 * 1024 * 1024;// 每个chunk的大小，2兆
        $(".uploadFile").click(function () {
            //得到上传的文件资源
            const file = $('#file')[0].files[0];
            if (!file) {
                alert('请先选择文件');
                return false;
            }
            $("#wai").css('display', 'block');
            document.getElementById('nei').style.width = '10%';
            document.getElementById('nei').innerHTML = '2%';

            // 需要分片成多少个
            const totalChunk = Math.ceil(file.size / chunkSize);
            //breakPointUploadFile(0, totalChunk, chunkSize, file);

            setTimeout(function (){
                breakPointUploadFile(0, totalChunk, chunkSize, file);
            }, 5);

        });
    });


    function breakPointUploadFile(i, totalChunk, chunkSize, file) {
        //当前上传文件块的起始位置
        const startLength = i * chunkSize;
        //当文件末尾不足一个分片时，取小者
        const endLength = Math.min(file.size, startLength + chunkSize);
        var formData = new FormData();
        //通过blobSlice获取分片文件
        formData.append("file", blobSlice.call(file, startLength, endLength));
        formData.append("startLength", startLength);
        formData.append("name", file.name);
        formData.append("id", i);
        formData.append("total", totalChunk);
        $.ajax({
            url: '/admin/tiktok/fileUpload',
            dataType: 'json',
            type: 'POST',
            async: false,
            data: formData,
            processData: false,
            contentType: false,
            success: function (data) {
                if (data.success) {
                    i++;
                    //当分片上传达到总分片个数，跳出递归
                    if (i < totalChunk) {
                        precent = 100 * (i / totalChunk);
                        if (precent > 100) {
                            precent = 100;
                        }
                        document.getElementById('nei').style.width = precent + '%';
                        document.getElementById('nei').innerHTML = Math.floor(precent) + '%　';
                        setTimeout(function () {
                            //采用递归调用该函数
                            breakPointUploadFile(i, totalChunk, chunkSize, file);
                        }, 0);
                    } else {
                        $("#video_url").val(data.url);
                        document.getElementById('nei').style.width = '100%';
                        document.getElementById('nei').innerHTML =  '100%　';
                        alert("文件上传成功");
                    }
                }
            },
            error: function (response) {
                console.log(response);
                alert("异常")
            }
        });
    }
</script>