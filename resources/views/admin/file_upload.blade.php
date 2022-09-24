<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>文件上传</title>
    <script src="https://cdn.bootcss.com/axios/0.18.0/axios.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/spark-md5/3.0.0/spark-md5.js"></script>
    <script>
        $(document).ready(() => {
            const submitBtn = $('#submitBtn');  //提交按钮
            const precentDom = $(".precent input")[0]; // 进度条
            const precentVal = $("#precentVal");  // 进度条值对应dom
            const pauseBtn = $('#pauseBtn');  // 暂停按钮
            // 每个chunk的大小，设置为1兆
            const chunkSize = 1 * 1024 * 1024;
            // 获取slice方法，做兼容处理
            const blobSlice = File.prototype.slice || File.prototype.mozSlice || File.prototype.webkitSlice;
            // 对文件进行MD5加密(文件内容+文件标题形式)
            const hashFile = (file) => {
                return new Promise((resolve, reject) => {
                    const chunks = Math.ceil(file.size / chunkSize);
                    let currentChunk = 0;
                    const spark = new SparkMD5.ArrayBuffer();
                    const fileReader = new FileReader();
                    function loadNext() {
                        const start = currentChunk * chunkSize;
                        const end = start + chunkSize >= file.size ? file.size : start + chunkSize;
                        fileReader.readAsArrayBuffer(blobSlice.call(file, start, end));
                    }
                    fileReader.onload = e => {
                        spark.append(e.target.result); // Append array buffer
                        currentChunk += 1;
                        if (currentChunk < chunks) {
                            loadNext();
                        } else {
                            console.log('finished loading');
                            const result = spark.end();
                            // 通过内容和文件名称进行md5加密
                            const sparkMd5 = new SparkMD5();
                            sparkMd5.append(result);
                            sparkMd5.append(file.name);
                            const hexHash = sparkMd5.end();
                            resolve(hexHash);
                        }
                    };
                    fileReader.onerror = () => {
                        console.warn('文件读取失败！');
                    };
                    loadNext();
                }).catch(err => {
                    console.log(err);
                });
            }

            // 提交
            submitBtn.on('click', async () => {
                var pauseStatus = false;
                var nowUploadNums = 0
                // 1.读取文件
                const fileDom = $('#file')[0];
                const files = fileDom.files;
                const file = files[0];
                if (!file) {
                    alert('没有获取文件');
                    return;
                }
                // 2.设置分片参数属性、获取文件MD5值
                const hash = await hashFile(file); //文件 hash
                const blockCount = Math.ceil(file.size / chunkSize); // 分片总数
                const axiosPromiseArray = []; // axiosPromise数组
                // 文件上传
                const uploadFile = () => {
                    const start = nowUploadNums * chunkSize;
                    const end = Math.min(file.size, start + chunkSize);
                    // 构建表单
                    const form = new FormData();
                    // blobSlice.call(file, start, end)方法是用于进行文件分片
                    form.append('file', blobSlice.call(file, start, end));
                    form.append('id', nowUploadNums);
                    form.append('hash', hash);
                    // ajax提交 分片，此时 content-type 为 multipart/form-data
                    const axiosOptions = {
                        onUploadProgress: e => {
                            nowUploadNums++;
                            // 判断分片是否上传完成
                            if (nowUploadNums < blockCount) {
                                setPrecent(nowUploadNums, blockCount);
                                uploadFile(nowUploadNums)
                            } else {
                                // 4.所有分片上传后，请求合并分片文件
                                axios.all(axiosPromiseArray).then(() => {
                                    setPrecent(blockCount, blockCount); // 全部上传完成
                                    axios.post('/api/tiktok/upload', {
                                        name: file.name,
                                        total: blockCount,
                                        hash
                                    }).then(res => {
                                        console.log(res.data, file);
                                        pauseStatus = false;
                                        $("#video_url_new").val(res.data);
                                        alert('上传成功');
                                    }).catch(err => {
                                        console.log(err);
                                    });
                                });
                            }
                        },
                    };
                    // 加入到 Promise 数组中
                    if (!pauseStatus) {
                        axiosPromiseArray.push(axios.post('/api/tiktok/upload', form, axiosOptions));
                    }

                }
                // 设置进度条
                function setPrecent(now, total) {
                    var prencentValue = ((now / total) * 100).toFixed(2)
                    precentDom.value = prencentValue
                    precentVal.text(prencentValue + '%')
                    precentDom.style.cssText = `background:-webkit-linear-gradient(top, #059CFA, #059CFA) 0% 0% / ${prencentValue}% 100% no-repeat`
                }
                // 暂停
                pauseBtn.on('click', (e) => {
                    pauseStatus = !pauseStatus;
                    e.currentTarget.value = pauseStatus ? '开始' : '暂停'
                    if (!pauseStatus) {
                        uploadFile(nowUploadNums)
                    }
                })
                uploadFile();
            });
        })
    </script>
    <style>
        /* 自定义进度条样式 */
        .precent input[type=range] {
            -webkit-appearance: none;
            /*清除系统默认样式*/
            width: 7.8rem;
            /* background: -webkit-linear-gradient(#ddd, #ddd) no-repeat, #ddd; */
            /*设置左边颜色为#61bd12，右边颜色为#ddd*/
            background-size: 75% 100%;
            /*设置左右宽度比例*/
            height: 0.6rem;
            /*横条的高度*/
            border-radius: 0.4rem;
            border: 1px solid #ddd;
            box-shadow: 0 0 10px rgba(0,0,0,.125) inset ;
        }

        /*拖动块的样式*/
        .precent input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none;
            /*清除系统默认样式*/
            height: .9rem;
            /*拖动块高度*/
            width: .9rem;
            /*拖动块宽度*/
            background: #fff;
            /*拖动块背景*/
            border-radius: 50%;
            /*外观设置为圆形*/
            border: solid 1px #ddd;
            /*设置边框*/
        }

    </style>
</head>

<body>
<div class="form-group">

    <label for="pid" class="col-sm-2 asterisk control-label">上传视频</label>
    <div class="col-sm-8">
        <div class="input-group">
            <input id="file" type="file" name="avatar" />
            <div style="padding: 20px 0;">
                <input id="submitBtn" type="button" value="点击上传" />   
                <input id="pauseBtn" type="button" value="暂停" />
            </div>
            <div class="precent">
                <input type="range" value="0" /><span id="precentVal">0%</span>
            </div>
            <input type="text" id="video_url_new" name="video_url_new" value="" class="form-control title" placeholder="视频url">
        </div>
    </div>
</div>
</body>

</html>