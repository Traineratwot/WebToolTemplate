window.gettext = (string) => {
	return Translations['messages'][''][string] ?? string
}
window.__ = window.gettext