// App.tsx
import React from 'react';
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Regmodule from './Components/Form/Regmodule';
import MainPage from './Components/Main/MainPage';

function App() {
  return (

      <div className="App">
        <Routes>
     
          <Route path="/auth/*" element={<Regmodule />} />

          <Route path="/main" element={<MainPage />} />

          <Route path="/" element={<Regmodule />} />

          <Route path="*" element={<div>Страница не найдена</div>} />
        </Routes>
      </div>

  );
}

export default App;