# ical2md

インターネット上のicalデータを読み込み、PicoCMS用のMarkdownを生成するツールです。

本来ならGoogleカレンダーのAPIなどを使うべきところですが、APIのコードを勉強している暇がないので一時しのぎに。

GoogleカレンダーのICALの読み込みに非常に時間がかかるので、オフラインで不定期に使う事を推奨します。

## つかいかた

### コンポーネントのインストール

まずは必要なコンポーネントをインストールします。

```cmd
> curl -Ss https://composer.org/installer | php
> php composer.phar install
```

Windows 10でPowershellのバージョンが5以下の環境をお使いの場合、`curl`を`curl.exe`に置換えてください。

### iniの設定

インストールが終わったら、iniファイルを設定します。

```ini
ics_url=読み込みたいカレンダーのICSファイルURL
scantags=Markdownファイルを出力したいイベントタイトルに設定する、ハッシュタグ
```

ハッシュタグはどういう風に設定するの？と疑問に思った方は、わたしの公開カレンダーなどを見ると分かりやすいかもです。

* [高見知英のイベントカレンダー](http://bit.ly/takamichie_event)

### Enjoy

最後に、phpファイルを実行します。

```cmd
> php main.php
```

Enjoy.