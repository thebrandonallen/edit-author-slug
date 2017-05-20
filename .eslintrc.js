module.exports = {
	root: true,
	extends: "plugin:wordpress/recommended",
	plugins: [
		"wordpress",
	],
	rules: {
		yoda: ["error", "always"]
	}
};
