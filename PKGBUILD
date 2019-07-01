# Maintainer: Jefferson Gonzalez <jgmdev@gmail.com>

pkgname=bluecontrol
pkgver=0.1
pkgrel=1
pkgdesc="Color temperature adjustment tool."
arch=('any')
url="http://github.com/jgmdev/bluecontrol"
license=('GPL')
depends=('php' 'php-sqlite' 'wmctrl' 'xorg-xrandr' 'chromium')
makedepends=('composer')
install="${pkgname}.install"
#source=( "future sources" )
#md5sums=( 'SKIP' )

build() {
  cd "${srcdir}/../"
  make
}

package() {
  cd "${srcdir}/../"
  make DESTDIR="$pkgdir" install
}
