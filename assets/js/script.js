// Modal Functions
function openLoginModal() {
  document.getElementById("loginModal").style.display = "block"
}

function closeLoginModal() {
  document.getElementById("loginModal").style.display = "none"
}

function openRegisterModal() {
  document.getElementById("registerModal").style.display = "block"
}

function closeRegisterModal() {
  document.getElementById("registerModal").style.display = "none"
}

// Close modal when clicking outside
window.onclick = (event) => {
  const loginModal = document.getElementById("loginModal")
  const registerModal = document.getElementById("registerModal")

  if (event.target == loginModal) {
    loginModal.style.display = "none"
  }
  if (event.target == registerModal) {
    registerModal.style.display = "none"
  }
}

// Mobile Navigation
function toggleMobileMenu() {
  const navMenu = document.querySelector(".nav-menu")
  const hamburger = document.querySelector(".hamburger")
  
  navMenu.classList.toggle("active")
  hamburger.classList.toggle("active")
}

// Sidebar Functions
function toggleSidebar() {
  const sidebar = document.querySelector(".sidebar")
  if (sidebar) {
    sidebar.classList.toggle("active")
  }
}

function toggleSubmenu(element) {
  const submenu = element.nextElementSibling
  if (submenu && submenu.classList.contains("sidebar-submenu")) {
    submenu.classList.toggle("active")
  }
}

// Search functionality
function searchTable(inputId, tableId) {
  const input = document.getElementById(inputId)
  const table = document.getElementById(tableId)

  if (!input || !table) return

  input.addEventListener("keyup", function () {
    const filter = this.value.toLowerCase()
    const rows = table.getElementsByTagName("tr")

    for (let i = 1; i < rows.length; i++) {
      const row = rows[i]
      const cells = row.getElementsByTagName("td")
      let found = false

      for (let j = 0; j < cells.length; j++) {
        if (cells[j].textContent.toLowerCase().indexOf(filter) > -1) {
          found = true
          break
        }
      }

      row.style.display = found ? "" : "none"
    }
  })
}

// Form Validation
document.addEventListener("DOMContentLoaded", () => {
  // Register form validation
  const registerForm = document.getElementById("registerForm")
  if (registerForm) {
    registerForm.addEventListener("submit", (e) => {
      const password = document.getElementById("reg_password").value
      const confirmPassword = document.getElementById("reg_confirm_password").value

      if (password !== confirmPassword) {
        e.preventDefault()
        showAlert("Password dan konfirmasi password tidak cocok!", "error")
        return false
      }

      if (password.length < 6) {
        e.preventDefault()
        showAlert("Password minimal 6 karakter!", "error")
        return false
      }
    })
  }

  // Initialize search functionality for all tables
  const searchInputs = document.querySelectorAll('[id^="search"]')
  searchInputs.forEach((input) => {
    const tableId = input.id.replace("search", "").toLowerCase() + "Table"
    searchTable(input.id, tableId)
  })
})

// Alert Functions
function showAlert(message, type = "info") {
  const alertDiv = document.createElement("div")
  alertDiv.className = `alert alert-${type}`
  alertDiv.innerHTML = `
    <span>${message}</span>
    <button onclick="this.parentElement.remove()" class="alert-close">&times;</button>
  `

  // Add alert styles if not already present
  if (!document.querySelector(".alert-styles")) {
    const style = document.createElement("style")
    style.className = "alert-styles"
    style.textContent = `
      .alert {
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: space-between;
        min-width: 300px;
        animation: slideInRight 0.3s ease;
      }
      .alert-success { background: #10b981; }
      .alert-error { background: #ef4444; }
      .alert-warning { background: #f59e0b; }
      .alert-info { background: #2563eb; }
      .alert-close {
        background: none;
        border: none;
        color: white;
        font-size: 20px;
        cursor: pointer;
        margin-left: 15px;
      }
      @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
      }
    `
    document.head.appendChild(style)
  }

  document.body.appendChild(alertDiv)

  // Auto remove after 5 seconds
  setTimeout(() => {
    if (alertDiv.parentElement) {
      alertDiv.remove()
    }
  }, 5000)
}

// Confirm delete
function confirmDelete(message = "Apakah Anda yakin ingin menghapus data ini?") {
  return confirm(message)
}

// File Upload Functions
function handleFileUpload(input, previewId) {
  const file = input.files[0]
  const preview = document.getElementById(previewId)

  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      if (preview) {
        if (file.type.startsWith("image/")) {
          preview.innerHTML = `<img src="${e.target.result}" style="max-width: 200px; max-height: 200px; border-radius: 8px;">`
        } else {
          preview.innerHTML = `<p>File: ${file.name}</p>`
        }
      }
    }
    reader.readAsDataURL(file)
  }
}

// Export Functions
function exportToExcel(tableId, filename) {
  const table = document.getElementById(tableId)
  if (!table) return

  const csv = []
  const rows = table.querySelectorAll("tr")

  for (let i = 0; i < rows.length; i++) {
    const row = []
    const cols = rows[i].querySelectorAll("td, th")

    for (let j = 0; j < cols.length - 1; j++) {
      // Exclude last column (actions)
      row.push(cols[j].innerText)
    }
    csv.push(row.join(","))
  }

  const csvFile = new Blob([csv.join("\n")], { type: "text/csv" })
  const downloadLink = document.createElement("a")
  downloadLink.download = filename + ".csv"
  downloadLink.href = window.URL.createObjectURL(csvFile)
  downloadLink.style.display = "none"
  document.body.appendChild(downloadLink)
  downloadLink.click()
  document.body.removeChild(downloadLink)
}

// Tournament Bracket Functions
function generateBracket(participants) {
  // Simple bracket generation logic
  const rounds = Math.ceil(Math.log2(participants.length))
  const bracket = []

  for (let round = 0; round < rounds; round++) {
    bracket[round] = []
    const matchesInRound = Math.pow(2, rounds - round - 1)

    for (let match = 0; match < matchesInRound; match++) {
      if (round === 0) {
        // First round - pair participants
        const participant1 = participants[match * 2]
        const participant2 = participants[match * 2 + 1]
        bracket[round][match] = {
          participant1: participant1 || { name: "BYE" },
          participant2: participant2 || { name: "BYE" },
          winner: null,
        }
      } else {
        // Subsequent rounds - winners from previous round
        bracket[round][match] = {
          participant1: null,
          participant2: null,
          winner: null,
        }
      }
    }
  }

  return bracket
}

function renderBracket(bracket, containerId) {
  const container = document.getElementById(containerId)
  if (!container) return

  container.innerHTML = ""

  const bracketDiv = document.createElement("div")
  bracketDiv.className = "bracket"

  bracket.forEach((round, roundIndex) => {
    const roundDiv = document.createElement("div")
    roundDiv.className = "bracket-round"

    const roundTitle = document.createElement("h3")
    roundTitle.textContent = `Round ${roundIndex + 1}`
    roundDiv.appendChild(roundTitle)

    round.forEach((match, matchIndex) => {
      const matchDiv = document.createElement("div")
      matchDiv.className = "bracket-match"

      const participant1Div = document.createElement("div")
      participant1Div.className = "bracket-participant"
      participant1Div.textContent = match.participant1?.name || "TBD"

      const participant2Div = document.createElement("div")
      participant2Div.className = "bracket-participant"
      participant2Div.textContent = match.participant2?.name || "TBD"

      if (match.winner) {
        if (match.winner === match.participant1?.name) {
          participant1Div.classList.add("winner")
        } else if (match.winner === match.participant2?.name) {
          participant2Div.classList.add("winner")
        }
      }

      matchDiv.appendChild(participant1Div)
      matchDiv.appendChild(participant2Div)
      roundDiv.appendChild(matchDiv)
    })

    bracketDiv.appendChild(roundDiv)
  })

  container.appendChild(bracketDiv)
}

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault()
    const target = document.querySelector(this.getAttribute("href"))
    if (target) {
      target.scrollIntoView({
        behavior: "smooth",
        block: "start",
      })
    }
  })
})

// Auto-hide alerts
document.addEventListener("DOMContentLoaded", () => {
  const alerts = document.querySelectorAll(".alert")
  alerts.forEach((alert) => {
    setTimeout(() => {
      alert.style.opacity = "0"
      setTimeout(() => {
        alert.remove()
      }, 300)
    }, 5000)
  })
})
