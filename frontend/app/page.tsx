"use client"

import { useEffect } from "react"
import { useRouter } from "next/navigation"

export default function HomePage() {
  const router = useRouter()

  useEffect(() => {
    router.push("/dashboard")
  }, [router])

  return (
    <div className="flex items-center justify-center h-screen">
      <div className="text-muted-foreground">Redirecting to dashboard...</div>
    </div>
  )
}
