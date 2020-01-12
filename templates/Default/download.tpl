<style>
.clr { clear: both; }
.dl { padding: 10px; background: #fff; }
.dl-title { font-size: 17px; margin-bottom: 10px; }
.dl-title span { color: #196EA5; }
.dl-info { float: left; border: 1px solid #ccc; background: #fcfcfc; }
.dl-list { margin: 0; padding: 10px 5px; margin-left: 20px; width: 400px; }
.dl-list li { font-size: 14px; font-family: sans-serif; line-height: 26px; clear: both; height: 38px; color: #111; }
.dl-list li span { float: right; padding: 1px 10px; background: #348CDC; color: #fff; }
.dl-list li span a { color: #ddd; }
.dl-show { float: right; border: 1px solid #ccc; width: 360px; display: block; height: auto; }
.dl-error { margin: 10px 0; border: 1px solid #800000; padding: 15px; background: #FFC0CB; }
.dl-file { margin-top: 10px; padding: 10px; text-align: center; }
.dl-file a { background: #3E4B51; color: #fff; padding: 10px; font-size: 14px; transition: .4s; }
.dl-file a:hover { background: #348CDC; color: #eee; transition: .4s; text-decoration: none; }
.dl-counter { }
.dl-counter span { font-size: 13px; font-family: verdana; font-weight: bold; }
.dl-related { background: #fff; padding: 10px; margin-top: 10px; }
</style>

<div class="dl">
	<h2 class="dl-title">Dosya: <span>{f-name}</span></h2>

	[direct]
	<div class="dl-error">
		Bu dosyaya direkt olarak erişemezsiniz. Dosyayı indirebilmek için lütfen bu linki ziyaret edin. {f-news-link}<br />
	</div>
	[/direct]

	[not-allowed]
	<div class="dl-error">
		Kullanıcı grubunuza tanımlanmış olan kısıtmalar nedeniyle bu dosyaya erişemezsiniz.
	</div>
	[/not-allowed]


	[not-direct]
	<div class="dl-info">
		<ul class="dl-list">
			<li>Dosya Adı:<span>{f-name}</span></li>
			<li>Dosya Uzantısı:<span>{f-ext}</span></li>
			<li>Dosya Klasörü:<span>{f-dir}</span></li>
			<li>Yükleyen:<span>{f-author}</span></li>
			<li>Tarih:<span>{f-date=d.m.Y}</span></li>
			<li>İndirilme:<span>{f-count}</span></li>
			<li>Boyut:<span>{f-size}</span></li>
			<li>Nereden:<span><a href="{f-news-link}">{f-news-title}</a></span></li>
			[not-static]<li>Kategorisi:<span>{f-cat-link}</span></li>[/not-static]
		</ul>
	</div>
	<div class="dl-show">
		[f-ext=zip]<p>Bu bir zip dosyası</p>[/f-ext]
		<p>Reklam Alanı</p>
		<p>[xfgiven_deneme] [xfvalue_deneme] [/xfgiven_deneme]</p>
		<div id="who-downloaded">
			<p><b>Kimler İndirdi?</b></p>
			<div>
				{who-downloaded}
			</div>
		</div>
	</div>
	<div class="clr"></div>
	<div class="dl-file">
		[timer]
		<a style="display:none" class="dl-button" href="#">Hemen İndir</a>
		<div class="dl-counter"></div>
		<input id="dlink" value="{f-dlink}" type="hidden" />
		[/timer]
		[notimer]
		<a class="dl-button" href="{f-dlink}">Hemen İndir</a>
		[/notimer]
	</div>
	[/not-direct]

</div>
<div class="clr"></div>

[related]
<div class="dl-related">
	Benzer Makaleler : <br />
	<ul>
		{related limit="10" template="relatednews"}
	</ul>
</div>
[/related]

[timer]
<script>
$(document).ready(function() {
	var count = parseInt('{f-counter}');
	var link = $("#dlink").val();
	$(".dl-counter").html('<span id="counter">0:' + count + '</span>');
	function counter() {
		if (count == 0) {
			clearInterval(counte);
			$(".dl-counter").fadeOut( 500, function() { $("a.dl-button").attr("href", link).fadeIn(); } );
		} else {
			if (count < 10) count = "0" + count;
			$(".dl-counter").html('<span id="counter">0:' + count + '</span>');
			count -= 1;
		}
	}
	var counte = setInterval(counter, 1000);
});
</script>
[/timer]