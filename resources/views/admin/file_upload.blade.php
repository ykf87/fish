<script src="https://cdn.bootcss.com/axios/0.18.0/axios.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/spark-md5/3.0.0/spark-md5.js"></script>
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
