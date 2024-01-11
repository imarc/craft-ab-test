
const currentUrl = window.location.href.split('?')[0]
const urlParams = new URLSearchParams(window.location.search);
const targetTest = urlParams.get('testname')
const targetOption = urlParams.get('option')
const useCookies = urlParams.get('useCookies')
let shownOption = null

const applicableTests = []
if (typeof tests != 'undefined') {
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
}

applicableTests.forEach(test => {
  const abTestHtml = document.querySelector(test.test.targetedSelector)
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
      abTestHtml.innerHTML = findOption(test.test.handle, test.options, useCookies)
    }
  }
})


function findOption(testHandle, options, useCookies) {
  let shownOption = null
  if (useCookies !== "false") {
    shownOption = localStorage.getItem("abtest" + testHandle)
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
  if(typeof gtag !== 'undefined') {
    gtag('event', testHandle, { testHandle : shownOption });
  }
  
  return innerHTML
}