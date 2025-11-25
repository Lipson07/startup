
import './App.scss'
import {Route, Routes} from 'react-router-dom';

import Regmodule from "./Components/Form/Regmodule.tsx";
function App() {


  return (
    <>
<Routes>
<Route path="/" element={<Regmodule/>}/>
</Routes>
    </>
  )
}

export default App
