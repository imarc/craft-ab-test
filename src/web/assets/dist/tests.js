
const currentUrl = window.location.href.split('?')[0]
const urlParams = new URLSearchParams(window.location.search);
const targetTest = urlParams.get('testname')
const targetOption = urlParams.get('option')
const useCookies = urlParams.get('useCookies')
let shownOption = null


console.log(targetTest);

const applicableTests = []
tests.forEach(test => {
  const targetedUrls = JSON.parse(test.test.targetedUrls)
  let urlFound = false
  targetedUrls.forEach(targetedUrl => {
    if (!urlFound) {
      const wildCardArray = targetedUrl.url.split("*")
      if (wildCardArray.length > 1) {
        let stillMatches = true
        wildCardArray.forEach(wildcard => {
          if (stillMatches) {
            if (!targetedUrl.url.includes(wildcard)) {
              stillMatches = false
            }
          }
        })
        if (stillMatches) {
          applicableTests.push(test)
          urlFound = true
        }
      }
      if (!urlFound && targetedUrl.url == currentUrl) {
        applicableTests.push(test)
        urlFound = true
      }
    }
  })
})

console.log(applicableTests)
applicableTests.forEach(test => {
  console.log(test.test.targetedSelector)
  const abTestHtml = document.querySelector(test.test.targetedSelector)
  console.log(abTestHtml)
  if (abTestHtml) {
    let displayed = false
    if (targetTest && targetOption) { // first try to display options in url parameters
      test.options.forEach(option => {
        if (test.test.handle == targetTest && option.handle == targetOption) {
          abTestHtml.innerHTML = option.innerHTML
          displayed = true
        }
      })
    }
    if (!displayed) { // then display options per weighting or cookies
      abTestHtml.innerHTML = findOption(test.test.handle, test.options, shownOption)
    }
  }
})
console.log(localStorage)

function findOption(testHandle, options) {
  let shownOption = null
  if (useCookies !== "false") {
    shownOption = localStorage.getItem("abtest" + testHandle)
    console.log(shownOption)
  }
  const randomNumber = Math.random() * 100
  let runningWeight = 0
  let innerHTML = ""
  let shown = false
  if (shownOption) {
    options.forEach(option => {
      if (!shown) {
        if (option.handle == shownOption) {
          innerHTML = option.innerHTML
          shown = true
        }
      }
    })
  }
  if (!shown) {
    options.forEach(option => {
      runningWeight += option.weight
      if (randomNumber <= runningWeight && !shown) {
        innerHTML = option.innerHTML
        shownOption = option.handle
        localStorage.setItem("abtest" + testHandle, shownOption)
        shown = true
      }
    })
  }
  gtag('event', testHandle, { testHandle : shownOption });
  return innerHTML
}