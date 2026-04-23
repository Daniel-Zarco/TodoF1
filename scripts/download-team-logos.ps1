# ============================================================
# TodoF1 – Download transparent SVG team logos
# Uses Wikipedia/Commons Special:FilePath (auto-redirect, no API parsing)
# Run from the project root: .\scripts\download-team-logos.ps1
# ============================================================

$dest = "public\images\teams"
New-Item -ItemType Directory -Force -Path $dest | Out-Null

# Each entry has a slug (local filename) and a list of candidate URLs tried in order.
# Using Special:FilePath which follows the redirect to the actual SVG file.
$logos = @(
    @{
        slug = "red-bull-racing"
        urls = @(
            "https://en.wikipedia.org/wiki/Special:FilePath/Red_Bull_Racing_logo.svg",
            "https://commons.wikimedia.org/wiki/Special:FilePath/Red_Bull_Racing_logo.svg"
        )
    },
    @{
        slug = "ferrari"
        urls = @(
            "https://commons.wikimedia.org/wiki/Special:FilePath/Scuderia_Ferrari_Logo.svg",
            "https://en.wikipedia.org/wiki/Special:FilePath/Scuderia_Ferrari_Logo.svg",
            "https://commons.wikimedia.org/wiki/Special:FilePath/Ferrari-Logo.svg"
        )
    },
    @{
        slug = "mercedes"
        urls = @(
            "https://en.wikipedia.org/wiki/Special:FilePath/Mercedes_AMG_Petronas_F1_Logo.svg",
            "https://commons.wikimedia.org/wiki/Special:FilePath/Mercedes_AMG_Petronas_F1_Logo.svg"
        )
    },
    @{
        slug = "mclaren"
        urls = @(
            "https://en.wikipedia.org/wiki/Special:FilePath/McLaren_Racing_logo.svg",
            "https://commons.wikimedia.org/wiki/Special:FilePath/McLaren_Racing_logo.svg"
        )
    },
    @{
        slug = "aston-martin"
        urls = @(
            "https://en.wikipedia.org/wiki/Special:FilePath/Aston_Martin_F1_team_logo.svg",
            "https://commons.wikimedia.org/wiki/Special:FilePath/Aston_Martin_F1_team_logo.svg"
        )
    },
    @{
        slug = "alpine"
        urls = @(
            "https://en.wikipedia.org/wiki/Special:FilePath/Alpine_F1_Team_Logo.svg",
            "https://commons.wikimedia.org/wiki/Special:FilePath/Alpine_F1_Team_Logo.svg"
        )
    },
    @{
        slug = "williams"
        urls = @(
            "https://en.wikipedia.org/wiki/Special:FilePath/Williams_Racing_logo.svg",
            "https://commons.wikimedia.org/wiki/Special:FilePath/Williams_Racing_logo.svg"
        )
    },
    @{
        slug = "rb"
        urls = @(
            "https://en.wikipedia.org/wiki/Special:FilePath/Racing_Bulls_F1_team_logo.svg",
            "https://en.wikipedia.org/wiki/Special:FilePath/Visa_Cash_App_RB_F1_Team_logo.svg",
            "https://commons.wikimedia.org/wiki/Special:FilePath/Racing_Bulls_F1_team_logo.svg"
        )
    },
    @{
        slug = "kick-sauber"
        urls = @(
            "https://en.wikipedia.org/wiki/Special:FilePath/Kick_Sauber_2024_team_logo.svg",
            "https://commons.wikimedia.org/wiki/Special:FilePath/Kick_Sauber_2024_team_logo.svg"
        )
    },
    @{
        slug = "haas"
        urls = @(
            "https://en.wikipedia.org/wiki/Special:FilePath/Haas_F1_team_logo.svg",
            "https://commons.wikimedia.org/wiki/Special:FilePath/Haas_F1_team_logo.svg"
        )
    }
)

$headers = @{
    "User-Agent" = "Mozilla/5.0 (TodoF1 portfolio project; logo download)"
    "Accept"     = "image/svg+xml,*/*"
}

foreach ($team in $logos) {
    $outFile = "$dest\$($team.slug).svg"
    $success = $false

    Write-Host "$($team.slug): " -NoNewline

    foreach ($url in $team.urls) {
        try {
            Invoke-WebRequest -Uri $url -OutFile $outFile -Headers $headers `
                -UseBasicParsing -MaximumRedirection 5 -TimeoutSec 15 -ErrorAction Stop

            # Verify we got actual SVG content (starts with < or BOM)
            $content = Get-Content $outFile -Raw -Encoding UTF8 -ErrorAction SilentlyContinue
            if ($content -and ($content.TrimStart().StartsWith("<") -or $content.StartsWith([char]0xFEFF))) {
                $size = (Get-Item $outFile).Length
                Write-Host "OK ($size bytes)" -ForegroundColor Green
                $success = $true
                break
            } else {
                Remove-Item $outFile -ErrorAction SilentlyContinue
            }
        } catch {
            # Try next URL
        }

        Start-Sleep -Milliseconds 800   # Be polite to Wikimedia
    }

    if (-not $success) {
        Write-Host "FAILED - will use fallback" -ForegroundColor Yellow
        if (Test-Path $outFile) { Remove-Item $outFile }
    }

    Start-Sleep -Milliseconds 400
}

Write-Host ""
Write-Host "Results in: $dest" -ForegroundColor Cyan
Get-ChildItem "$dest\*.svg" | ForEach-Object { Write-Host "  $($_.Name)  ($($_.Length) bytes)" }
