<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ZhuyinController extends Controller
{
    public function convert(Request $request)
    {
        $request->validate([
            'word' => 'required|string',
        ]);

        $text = $request->input('word');
        $results = [];

        // Split text into characters
        // mb_str_split is available in PHP 7.4+
        $chars = mb_str_split($text);

        foreach ($chars as $char) {
            // Skip non-Chinese characters (simple check)
            if (!preg_match('/\p{Han}/u', $char)) {
                $results[] = [
                    'char' => $char,
                    'zhuyin' => [$char] // Return char itself as "pronunciation"
                ];
                continue;
            }

            $zhuyins = $this->fetchZhuyin($char);

            $results[] = [
                'char' => $char,
                'zhuyin' => $zhuyins
            ];
        }

        return response()->json($results);
    }

    private function fetchZhuyin($char, $depth = 0)
    {
        // Prevent infinite recursion
        if ($depth > 3) {
            return [$char];
        }

        try {
            // Fetch from Moedict
            // Using the 'a' API which returns JSON
            $response = Http::get("https://www.moedict.tw/a/" . urlencode($char) . ".json");

            if ($response->successful()) {
                $data = $response->json();
                $zhuyins = [];

                if (isset($data['h'])) {
                    foreach ($data['h'] as $heteronym) {
                        if (isset($heteronym['b'])) {
                            // Clean up bopomofo (remove spaces, etc if needed)
                            // Moedict 'b' field: "ㄊㄧㄢˊ"
                            $zhuyins[] = $heteronym['b'];
                        }
                        
                        // Check for variant definition in 'd' (definitions)
                        if (isset($heteronym['d'])) {
                            foreach ($heteronym['d'] as $def) {
                                if (isset($def['f'])) {
                                    // Clean up definition string (remove ` and ~)
                                    $cleanDef = str_replace(['`', '~'], '', $def['f']);
                                    
                                    // Pattern: 「X」的異體字。
                                    if (preg_match('/「([^」]+)」的異體字/', $cleanDef, $matches)) {
                                        $standardChar = $matches[1];
                                        
                                        // Recursively fetch standard char
                                        $variantZhuyins = $this->fetchZhuyin($standardChar, $depth + 1);
                                        if (!empty($variantZhuyins) && $variantZhuyins[0] !== $standardChar) {
                                            $zhuyins = array_merge($zhuyins, $variantZhuyins);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }

                // Remove duplicates
                $zhuyins = array_unique($zhuyins);
                
                // If no zhuyin found, return char
                if (empty($zhuyins)) {
                     return [$char];
                }

                return array_values($zhuyins);
            }
        } catch (\Exception $e) {
            // Log error if needed
        }

        return [$char];
    }
}
