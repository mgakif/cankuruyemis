import React, { useState } from 'react';

interface LoginFormProps {
  onLogin: () => void;
}

const LoginForm: React.FC<LoginFormProps> = ({ onLogin }) => {
  const [username, setUsername] = useState('');
  const [password, setPassword] = useState('');
  const [error, setError] = useState('');

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (username === 'safak' && password === '123654') {
      onLogin();
    } else {
      setError('Hatalı kullanıcı adı veya şifre!');
    }
  };

  return (
    <div className="min-h-screen flex items-center justify-center bg-brand-50 px-4">
      <div className="max-w-md w-full bg-white rounded-2xl shadow-2xl overflow-hidden border border-brand-100">
        <div className="bg-brand-900 py-8 px-4 text-center">
          <div className="w-20 h-20 bg-white rounded-full mx-auto mb-4 flex items-center justify-center text-4xl shadow-inner border-2 border-brand-500">
            🌰
          </div>
          <h1 className="text-2xl font-bold text-white tracking-tight">Can Kuruyemiş</h1>
          <p className="text-brand-300 text-sm uppercase tracking-widest mt-1">Asistan Girişi</p>
        </div>
        
        <form onSubmit={handleSubmit} className="p-8 space-y-6">
          {error && (
            <div className="bg-red-50 text-red-600 text-sm p-3 rounded-lg border border-red-100 animate-pulse font-medium">
              {error}
            </div>
          )}
          
          <div>
            <label className="block text-sm font-bold text-brand-900 mb-2">Kullanıcı Adı</label>
            <input
              type="text"
              className="w-full px-4 py-3 rounded-xl border-2 border-brand-100 text-brand-950 font-medium placeholder-gray-400 focus:bg-brand-50/50 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-all bg-white"
              placeholder="Kullanıcı adınız"
              value={username}
              onChange={(e) => setUsername(e.target.value)}
              required
            />
          </div>
          
          <div>
            <label className="block text-sm font-bold text-brand-900 mb-2">Şifre</label>
            <input
              type="password"
              className="w-full px-4 py-3 rounded-xl border-2 border-brand-100 text-brand-950 font-medium placeholder-gray-400 focus:bg-brand-50/50 focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none transition-all bg-white"
              placeholder="••••••"
              value={password}
              onChange={(e) => setPassword(e.target.value)}
              required
            />
          </div>
          
          <button
            type="submit"
            className="w-full bg-brand-600 hover:bg-brand-700 text-white font-bold py-4 px-4 rounded-xl shadow-lg transition-all active:transform active:scale-95 flex justify-center items-center gap-2 text-lg"
          >
            <span>Dükkana Gir</span>
            <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
              <path fillRule="evenodd" d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z" clipRule="evenodd" />
            </svg>
          </button>
        </form>
        
        <div className="bg-gray-50 px-8 py-4 border-t border-brand-50 text-center">
          <p className="text-xs text-gray-400 font-medium tracking-tight">Can Kuruyemiş Dijital Yönetim Sistemi &copy; 2024</p>
        </div>
      </div>
    </div>
  );
};

export default LoginForm;