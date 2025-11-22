import { useState } from 'react'
import './App.css'

function App() {
  const [count, setCount] = useState(0)

  return (
    <div className="min-h-screen bg-gradient-to-br from-amber-50 to-orange-100 flex items-center justify-center">
      <div className="text-center">
        <h1 className="text-5xl font-bold text-amber-900 mb-4">
          Indo Cafe
        </h1>
        <p className="text-xl text-amber-700 mb-8">
          Rasa Nusantara, Gaya Masa Kini
        </p>
        <button
          onClick={() => setCount((count) => count + 1)}
          className="px-6 py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-lg transition-colors"
        >
          Count: {count}
        </button>
        <p className="mt-8 text-amber-600">
          Development in progress... 🚀
        </p>
      </div>
    </div>
  )
}

export default App
