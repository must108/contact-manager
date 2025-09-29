(() => {
  const API_BASE = './api/';

  // Elements
  const authView = id('authView');
  const appView = id('appView');
  const loginForm = id('loginForm');
  const registerForm = id('registerForm');
  const addContactForm = id('addContactForm');
  const loginMsg = id('loginMessage');
  const registerMsg = id('registerMessage');
  const addMsg = id('addMessage');
  const searchMsg = id('searchMessage');
  const searchInput = id('searchInput');
  const tbody = qs('#contactsTable tbody');
  const emptyState = id('emptyState');
  const userGreeting = id('userGreeting');
  const logoutBtn = id('logoutBtn');

  const showRegisterBtn = id('showRegister');
  const showLoginBtn = id('showLogin');
  const loginSection = id('loginSection');
  const registerSection = id('registerSection');

  let currentUser = null;
  let searchTimer = null;

  function id(x){ return document.getElementById(x); }
  function qs(sel,scope=document){ return scope.querySelector(sel); }
  function setMsg(el, msg, ok=false){ if(!el) return; el.textContent=msg; el.classList.toggle('success', !!ok); }
  function val(idStr){ return id(idStr).value.trim(); }

  async function api(endpoint, payload) {
    const res = await fetch(API_BASE + endpoint, {
      method: 'POST',
      headers: { 'Content-Type':'application/json' },
      body: JSON.stringify(payload||{})
    });
    const data = await res.json().catch(()=>({error:'Invalid JSON'}));
    if(!res.ok || data.error){
      throw new Error(data.error || ('HTTP '+res.status));
    }
    return data;
  }

  // Auth
  async function handleLogin(e){
    e.preventDefault();
    setMsg(loginMsg, '');
    const login = val('login_login');
    const password = val('login_password');
    if(!login || !password){ return setMsg(loginMsg,'Enter credentials'); }
    try {
      const data = await api('Login.php', { login, password });
      currentUser = { id: data.id, firstName: data.firstName, lastName: data.lastName };
      persistUser();
      enterApp();
    } catch (err){
      setMsg(loginMsg, err.message);
    }
  }

  async function handleRegister(e){
    e.preventDefault();
    setMsg(registerMsg,'');
    const firstName = val('reg_first');
    const lastName = val('reg_last');
    const login = val('reg_login');
    const password = val('reg_password');
    if(!firstName||!lastName||!login||!password){ return setMsg(registerMsg,'All fields required'); }
    try {
      const data = await api('Register.php', { firstName, lastName, login, password });
      currentUser = { id: data.id, firstName: data.firstName, lastName: data.lastName };
      persistUser();
      enterApp();
    } catch (err){
      setMsg(registerMsg, err.message);
    }
  }

  function persistUser(){
    if(currentUser){
      localStorage.setItem('user', JSON.stringify(currentUser));
    }
  }

  function restoreUser(){
    try {
      const raw = localStorage.getItem('user');
      if(raw){
        const u = JSON.parse(raw);
        if(u && u.id) {
          currentUser = u;
          enterApp();
        }
      }
    } catch(e){}
  }

  function enterApp(){
    if(!currentUser) return;
    hideAllViews();
    appView.classList.remove('hidden');
    userGreeting.textContent = `Welcome ${currentUser.firstName} ${currentUser.lastName}!`;
    setMsg(addMsg,'');
    setMsg(searchMsg,'');
    loadContacts();
  }

  function hideAllViews(){
    authView.classList.add('hidden');
    appView.classList.add('hidden');
  }

  function showAuth(mode='login'){
    hideAllViews();
    authView.classList.remove('hidden');
    if(mode==='register'){
      loginSection.classList.add('hidden');
      registerSection.classList.remove('hidden');
    } else {
      registerSection.classList.add('hidden');
      loginSection.classList.remove('hidden');
    }
    setMsg(loginMsg,'');
    setMsg(registerMsg,'');
  }

  function logout(){
    localStorage.removeItem('user');
    currentUser = null;
    showAuth('login'); // go straight back to login
    loginForm.reset();
    registerForm.reset();
    addContactForm.reset();
    tbody.innerHTML='';
    emptyState.classList.add('hidden');
  }

  // Contacts
  async function loadContacts(term=''){
    if(!currentUser) return;
    try {
      setMsg(searchMsg, term ? 'Searching...' : '');
      const data = await api('SearchContacts.php', { userId: currentUser.id, search: term });
      renderContacts(data.results || []);
      setMsg(searchMsg,'',true);
    } catch(err){
      renderContacts([]);
      setMsg(searchMsg, err.message);
    }
  }

  function renderContacts(list){
    tbody.innerHTML = '';
    if(!list.length){
      emptyState.classList.remove('hidden');
      return;
    }
    emptyState.classList.add('hidden');
    const frag = document.createDocumentFragment();
    list.forEach(c => {
      const tr = document.createElement('tr');
      tr.innerHTML = `
        <td>${escapeHtml(c.FirstName)} ${escapeHtml(c.LastName)}</td>
        <td>${escapeHtml(c.Phone)}</td>
        <td>${escapeHtml(c.Email)}</td>
        <td>
          <div class="action-buttons">
            <button class="edit-btn" data-id="${c.contactId}">Edit</button>
            <button class="action-btn" data-id="${c.contactId}">Delete</button>
          </div>
        </td>
      `;
      frag.appendChild(tr);
    });
    tbody.appendChild(frag);
  }

  async function handleAddContact(e){
    e.preventDefault();
    if(!currentUser) return;
    setMsg(addMsg,'Adding...');
    const firstName = val('add_first');
    const lastName = val('add_last');
    const phone = val('add_phone');
    const email = val('add_email');
    if(!firstName||!lastName||!phone||!email){
      return setMsg(addMsg,'All fields required');
    }
    try {
      await api('AddContact.php', { userId: currentUser.id, firstName, lastName, phone, email });
      addContactForm.reset();
      setMsg(addMsg,'Added', true);
      loadContacts(searchInput.value.trim());
    } catch(err){
      setMsg(addMsg, err.message);
    }
  }

  async function deleteContact(id){
    if(!currentUser) return;
    try {
      await api('DeleteContact.php', { userId: currentUser.id, contactId: id });
      loadContacts(searchInput.value.trim());
    } catch(err){
      setMsg(searchMsg, err.message);
    }
  }

  function debounceSearch(){
    clearTimeout(searchTimer);
    searchTimer = setTimeout(()=> {
      loadContacts(searchInput.value.trim());
    }, 350);
  }

  // Utilities
  function escapeHtml(str){
    return String(str||'').replace(/[&<>"']/g, s => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[s]));
  }

  // Event listeners
  loginForm.addEventListener('submit', handleLogin);
  registerForm.addEventListener('submit', handleRegister);
  addContactForm.addEventListener('submit', handleAddContact);
  logoutBtn.addEventListener('click', logout);

  showRegisterBtn.addEventListener('click', ()=> showAuth('register'));
  showLoginBtn.addEventListener('click', ()=> showAuth('login'));

  searchInput.addEventListener('input', debounceSearch);

  // ✅ Contact table event delegation
  tbody.addEventListener('click', e=>{
    // Delete
    if(e.target.closest('button.action-btn')){
      const id = parseInt(e.target.dataset.id,10);
      if(Number.isInteger(id)){
        if(confirm('Delete this contact?')){
          deleteContact(id);
        }
      }
    }

    // Edit
    if(e.target.closest('button.edit-btn')){
      const id = parseInt(e.target.dataset.id,10);
      if(Number.isInteger(id)){
        const tr = e.target.closest('tr');
        const cells = tr.querySelectorAll('td');

        const fullName = cells[0].textContent.trim().split(" ");
        const firstName = fullName[0] || "";
        const lastName  = fullName.slice(1).join(" ") || "";
        const phone     = cells[1].textContent.trim();
        const email     = cells[2].textContent.trim();

        tr.innerHTML = `
          <td><input type="text" value="${firstName}" class="edit-first"></td>
          <td><input type="text" value="${lastName}" class="edit-last"></td>
          <td><input type="tel" value="${phone}" class="edit-phone"></td>
          <td><input type="email" value="${email}" class="edit-email"></td>
          <td>
            <div class="action-buttons">
              <button class="save-btn" data-id="${id}">Save</button>
              <button class="cancel-btn">Cancel</button>
            </div>
          </td>
        `;
      }
    }

    // Save
    if(e.target.closest('button.save-btn')){
      const id = parseInt(e.target.dataset.id,10);
      const tr = e.target.closest('tr');
      const firstName = tr.querySelector('.edit-first').value.trim();
      const lastName  = tr.querySelector('.edit-last').value.trim();
      const phone     = tr.querySelector('.edit-phone').value.trim();
      const email     = tr.querySelector('.edit-email').value.trim();

      if(firstName && lastName && phone && email){
        api('UpdateContact.php', {
          userId: currentUser.id,
          contactId: id,
          firstName,
          lastName,
          phone,
          email
        }).then(()=>{
          loadContacts(searchInput.value.trim());
        }).catch(err=>{
          setMsg(searchMsg, err.message);
        });
      } else {
        alert("All fields required.");
      }
    }

    // Cancel
    if(e.target.closest('button.cancel-btn')){
      loadContacts(searchInput.value.trim());
    }
  });

  // Init
  document.addEventListener('DOMContentLoaded', () => {
    restoreUser();
    if(!currentUser){
      showAuth('login'); // go directly to login page
    }
  });
})();
