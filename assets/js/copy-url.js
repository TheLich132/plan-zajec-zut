function copyUrlToClipboard() {
    const url = window.location.href;
    navigator.clipboard.writeText(url).then(() => {
        console.log("URL copied to clipboard");
    }).catch((err) => {
        console.error("Could not copy URL to clipboard:", err);
    });
}