

const innerHTMLs = document.querySelectorAll("textarea")

const submitButton = document.querySelector("button[type='submit']")
submitButton.addEventListener("click", checkUrls)

function checkUrls(event) {
    event.preventDefault()

    let message = ""

    innerHTMLs.forEach((el) => {
        if (el.name.includes("innerHTML")) {
            let urlPos = el.value.matchAll("https://")
            urlPos.forEach((match) => {
                const endUrlPos = el.value.indexOf('"', match.index)
                message += el.value.substring(match.index, endUrlPos) + "\n"
            })
            urlPos = el.value.matchAll("http://")
            urlPos.forEach((match) => {
                const endUrlPos = el.value.indexOf('"', match.index)
                message += el.value.substring(match.index, endUrlPos) + "\n"
            })
        }
    })
    if (message.length) {
        message = "The inner HTML contains the following absolute URLs. Make are you sure you want to save?\n\n" + message
        if (confirm(message)) {
            const form = document.querySelector('#main-form')
            form.submit()
        }
    }
}

