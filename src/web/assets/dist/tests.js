
const currentUrl = window.location.href.split('?')[0]
const urlParams = new URLSearchParams(window.location.search);
const targetTest = urlParams.get('testname')
const targetOption = urlParams.get('option')
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
  console.log('#' + test.test.targetedElementId)
  const abTestHtml = document.querySelector('#' + test.test.targetedElementId)
  console.log(abTestHtml)
  let displayed = false
  if (targetTest && targetOption) { // first try to display options in url parameters
    test.options.forEach(option => {
      if (test.test.handle == targetTest && option.handle == targetOption) {
        abTestHtml.innerHTML = option.innerHTML
        displayed = true
      }
    })
  }
  if (!displayed) { // then display options per weighting
    const randomNumber = Math.random() * 100
    let runningWeight = 0
    test.options.forEach(option => {
      runningWeight += option.weight
      if (randomNumber <= runningWeight && !displayed) {
        abTestHtml.innerHTML = option.innerHTML
        displayed = true
      }
    })
  }
})
