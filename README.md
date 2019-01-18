# Downloads
<img src="https://img.shields.io/badge/dle-13.0+-007dad.svg"> <img src="https://img.shields.io/badge/lang-tr-ce600f.svg"> <img src="https://img.shields.io/badge/lang-en-ce600f.svg"> <img src="https://img.shields.io/badge/license-GPL-60ce0f.svg">

Eklenti olarak yüklediğiniz dosyaların ayrı sayfada indirilmesini sağlayabilirsiniz. Makale ve sabit sayfalarda kullanılabilir. Eklentiye indirmek için tıklandığında yeni bir sayfa açılır ve aynı şekilde buradan indirilmesini sağlayabilirsiniz. İsterseniz tamamen ayrı bir sayfa tasarımı da kullanabilirsiniz.

## Kurulum
**1)** .htaccess dosyasını açarak `RewriteEngine On` satırının altına ekleyin

```bash
# Downloads
RewriteRule ^download/([0-9]+)/?(static)?$ index.php?do=download.php&id=$1&area=$2 [L]
RewriteRule ^file/([0-9]+)$ index.php?do=downloads&id=$1 [L]
RewriteRule ^file/([a-z0-9]{32})$ index.php?do=downloads&hash=$1 [L]
RewriteRule ^file/([a-zA-Z0-9\w\-\.]+)$ index.php?do=downloads&name=$1 [L]
RewriteRule ^sfile/([0-9]+)$ index.php?do=downloads&id=$1&area=static [L]
RewriteRule ^sfile/([a-z0-9]{32})$ index.php?do=downloads&hash=$1&area=static [L]
RewriteRule ^sfile/([a-zA-Z0-9\w\-\.]+)$ index.php?do=downloads&name=$1&area=static [L]
```

## Konfigürasyon
Modül ile ilgili tüm ayarları yönetici panelinden yapabilirsiniz.

Sabit sayfalar ve makale dosyaları için:
```
{f-name} : Dosya adı
{f-size} : Dosya boyutu ( kb, mb soneki ile )
{f-author} : Dosyayı yükleyen kullanıcının adı
{f-date} : m.d.Y formatında tarih
{f-count} : Dosyanın indirilme sayısı
{f-counter} : Geri sayım süresi
{f-link} : Dosyanın indirme linki
{f-seo-link} : Dosyanın indirme linki ( SEF )
{f-ext} : Dosyanın uzantısı. ( Örnek: zip, rar, apk )
{f-dir} : Dosyanın yüklü olduğu klösör ismi
{f-url} : Dosyanın direkt url si. ( Örnek: http://..../uploads/files/dosya.zip )
[f-ext=zip,rar,apk] Eğer görüntülenen dosya uzantısı zip, rar ya da apk ise gözükür [/f-ext]
```

Sadece Makale Dosyaları için
```
{f-news-title} : Dosyanın yüklü olduğu makalenin başlığı
{f-dlink} : Dosyanın indirme linki. ( Üstteki ikisinin otomatik kullanılabilir hali )
{f-news-link} : Dosyanın yüklü olduğu makalenin link
{f-cat-link} : Dosyanın yüklü olduğu makalenin ait olduğu kategorinin linki
{f-cat-name} : Dosyanın yüklü olduğu makalenin ait olduğu kategorinin adı
{related limit="10" template="relatednews"} : Makaleye ait benzer makaleler. Limit ve şablon parametresi
```

Kontrol Tagların
```
[direct] Dosya linkine direkt olarak ulaşıldığında [/direct]
[not-direct] Dosya linkine direkt olarak ulaşılmadığında [/not-direct]
[not-allowed] Kullanıcı grubunun indirme izni yoksa gözükür [/not-allowed]
[allowed] Kullanıcı grubunun indirme izni varsa gözükür [/allowed]
[timer] Geri sayım özelliği açıksa [/timer]
[notimer] Geri sayım özelliği kapalıysa [/notimer]
[related] Benzer makaleler özelliği açıksa [/related]
[not-static] Makaleye yüklenmiş dosya ise [/not-static]
[static] Sabit sayfaya yüklenmiş dosya ise [/static]
```

Ayarlarda reklam gösterimi aktif ise banner taglarını; Custom taglarını ve ilave alan taglarını kullanabilirsiniz.
```
[banner_x] {banner_x} [/banner_x]
{custom ...}
[xfgiven_x] [xfvalue_x] [/xfgiven_x]
```

`{f-url}` tagı ile dosyanın direkt linkini kullanabilirsiniz. Fakat sistem yüklenen dosyaların bazınlarına direkt erişim izni vermiyor.
Kullanacağınız dosya uzantılarını aşağıdaki gibi ekleyerek bu kısıtlamayı aşabilirsiniz.

`/uploads/files/` klasöründeki `.htaccess` dosyasını açıp
`(avi|divx|mp3|mp4|flv|swf|wmv|m4v|m4a|mov|mkv|3gp|f4v)`

Bu şekilde ( pdf, doc, ppt, pptx, docx eklenmiş ) düzenleme yapabilirsiniz.
`(pdf|doc|ppt|pptx|docx|avi|divx|mp3|mp4|flv|swf|wmv|m4v|m4a|mov|mkv|3gp|f4v)`


## Ekran Görüntüleri
![Ekran 1](docs/screen1.png?raw=true)
![Ekran 2](docs/screen2.png?raw=true)

## Tarihçe

| Version | Tarih | Uyumluluk | Yenilikler |
| ------- | ----- | --------- | ---------- |
| **1.7** | 18.01.2019 | 13.0+ | Eklenti sistemine geçildi. DLE 13.0 ve üstü sürümler için uyarlama yapıldı. |
| **1.6.1** | 18.06.2018 | 12.1, 12.0 | İngilizce çeviri eklendi |
| **1.6** | 14.01.2018 | 12.1, 12.0 | DLE 12.0 için uyumluluk eklendi.<br>Yeni ilave alan türlerinin çalışması sağlandı.<br>ID ile erişim hatası giderildi. |
| **1.5** | 20.12.2016 | 11.x, 10.x | DLE 11.2 için uyumluluk eklendi. |
| **1.4.1** | 28.12.2015 | 11.1, 11.0, 10.x | DLE 10.6 için uyumluluk eklendi. |
| **1.4** | 25.09.2015 | 10.5, 10.4, 10.3, 10.2 | Dosya uzantısı ve yüklü olduğu klasörü için yeni tag eklendi.<br>Belirli uzantılardaki dosyaları gösterebileceğiniz kontrol tagı eklendi.<br>Dosyanın direkt linkini kullanabilmek için yeni düzenlemeler eklendi.<br>DLE 10.5 ile uyumlu hale getirildi. |
| **1.3** | 31.01.2015 | 10.4, 10.3, 10.2 | Linklerde dosya isimleri kullanılabilir olarak ayarlandı.<br>Üç farklı dosya linki desteği sağlandı.<br>Dosyalara verilecek direkt linkteki erişim hatası düzeltildi.<br>Admin paneldeki Arama motorlarına indexlemeyi kapat ayarının düzgün çalışması sağlandı.<br>Şablon dosyasındaki örnek kodlar kaldırıldı.<br>Dosya bulunamadığında, 404 belirteci eklendi ve başlıklar düzenlendi. |
| **1.2** | 07.12.2014 | 10.4, 10.3, 10.2 | Dosya tarihindeki hata giderildi<br>Admin paneldeki config yazım hata uyarı giderildi<br>Custom kullanım özelliği eklendi ( download ve download_page şablonlarında custom kodlarını kullanabilirsiniz )<br>Benzer makale kullanımı için {related} tagı eklendi. |
| **1.1** | 06.12.2014 | 10.4, 10.3, 10.2 | Geri sayım özelliği için açma kapama seçeneği eklendi.<br>İndirme linkinde ID yerine HASH kullanabilme seçeneği eklendi.<br>Şablonda kullanılan tarih fonksiyonu değiştirildi |
| **1.0** | 24.11.2014 | 10.4, 10.3, 10.2 | DLE 10.3 ve 10.2 uyumluluğu<br>Şablon sistemi kullanma<br>Kolay kurulum ve ayarlama<br>Kolay açıp / kapatma imkanı |
