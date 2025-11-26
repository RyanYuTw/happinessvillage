import Swal from 'sweetalert2'

// 成功訊息
export const showSuccess = (message, title = '成功') => {
    return Swal.fire({
        icon: 'success',
        title: title,
        text: message,
        confirmButtonText: '確定',
        confirmButtonColor: '#3b82f6',
    })
}

// 錯誤訊息
export const showError = (message, title = '錯誤') => {
    return Swal.fire({
        icon: 'error',
        title: title,
        text: message,
        confirmButtonText: '確定',
        confirmButtonColor: '#ef4444',
    })
}

// 資訊訊息
export const showInfo = (message, title = '提示') => {
    return Swal.fire({
        icon: 'info',
        title: title,
        text: message,
        confirmButtonText: '確定',
        confirmButtonColor: '#3b82f6',
    })
}

// 警告訊息
export const showWarning = (message, title = '警告') => {
    return Swal.fire({
        icon: 'warning',
        title: title,
        text: message,
        confirmButtonText: '確定',
        confirmButtonColor: '#f59e0b',
    })
}

// 確認對話框
export const showConfirm = (message, title = '確認') => {
    return Swal.fire({
        icon: 'question',
        title: title,
        text: message,
        showCancelButton: true,
        confirmButtonText: '確定',
        cancelButtonText: '取消',
        confirmButtonColor: '#3b82f6',
        cancelButtonColor: '#6b7280',
        reverseButtons: true,
    })
}

// 簡單的 alert（用於替換原生 alert）
export const showAlert = (message) => {
    return Swal.fire({
        text: message,
        confirmButtonText: '確定',
        confirmButtonColor: '#3b82f6',
    })
}

export default Swal
