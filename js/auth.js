(function () {
  const LS_USERS = 'users';
  const LS_USER = 'user';

  const getUsers = () => JSON.parse(localStorage.getItem(LS_USERS) || '[]');
  const setUsers = (arr) => localStorage.setItem(LS_USERS, JSON.stringify(arr));

  // Trả về object user an toàn để lưu ở LS_USER (không chứa password)
  const publicUser = (u) => ({
    id: u.id,
    fullname: u.fullname,
    email: u.email,
    number_phone: u.number_phone || u.phone || '',
    address: u.address || ''
  });

  const setCurrentUser = (user) => {
    localStorage.setItem(LS_USER, JSON.stringify(publicUser(user)));
    window.dispatchEvent(new CustomEvent('authChanged', { detail: { user } }));
  };
  const getCurrentUser = () => JSON.parse(localStorage.getItem(LS_USER) || 'null');

  const register = ({ fullname, email, password, number_phone = '', address = '' }) => {
    const users = getUsers();
    if (users.some(u => u.email.toLowerCase() === email.toLowerCase())) {
      throw new Error('Email đã tồn tại');
    }
    const user = { id: Date.now(), fullname, email, password, number_phone, address };
    users.push(user);
    setUsers(users);
    setCurrentUser(user);
    return user;
  };

  const login = ({ email, password }) => {
    const users = getUsers();
    const user = users.find(u => u.email.toLowerCase() === email.toLowerCase() && u.password === password);
    if (!user) throw new Error('Email hoặc mật khẩu không đúng');
    setCurrentUser(user);
    return user;
  };

  const logout = () => {
    localStorage.removeItem(LS_USER);
    window.dispatchEvent(new CustomEvent('authChanged', { detail: { user: null } }));
  };

  window.Auth = { register, login, logout, getUsers, getCurrentUser };
  
  // Global: đồng bộ hành vi Đăng xuất trên mọi trang (bắt click vào [data-logout])
  document.addEventListener('click', (e) => {
    const a = e.target && e.target.closest && e.target.closest('[data-logout]');
    if (!a) return;
    e.preventDefault();
    try { logout(); } catch { localStorage.removeItem(LS_USER); }
    // Chuyển về trang chủ sau khi đăng xuất
    location.href = 'index.html';
  });
})();