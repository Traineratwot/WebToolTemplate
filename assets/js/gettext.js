window.gettext = (string) => {
	if(window.hasOwnProperty('Translations')) {
		return Translations['messages'][''][string] ?? string
	}
	return string
}
window.__ = window.gettext