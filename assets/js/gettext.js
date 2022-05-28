window.gettext = (string) => {
	if(Translations !== undefined) {
		return Translations['messages'][''][string] ?? string
	}
	return string
}
window.__ = window.gettext